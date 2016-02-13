<?php

/**
 * This file is part of the Collate extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar 'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */
class SpecialCollate extends ManuscriptDeskBaseSpecials {

    /**
     * This code can run in a few different contexts:
     * 
     * 1: on normal entry, no request is posted, and the default page, with all the collections and manuscripts of the current user is shown
     * 2: on submit, a collation table is constructed, old tempcollate collate data is deleted, the current collate data is stored in the tempcollate table, and the table is shown
     * 3: when redirecting to start, the default page is shown
     * 4: when saving the table, the data is retrieved from the tempcollate table, saved to the collations table, a new wiki page is created, and the user is redirected to this page 
     * 
     */
    public $article_url;
    private $minimum_manuscripts;
    private $maximum_manuscripts;
    private $user_name;
    private $full_manuscripts_url;
    private $posted_titles_array = array();
    private $collection_array = array();
    private $collection_hidden_array = array();
    private $save_table = false;
    private $error_message = false;
    private $manuscripts_namespace_url;
    private $redirect_to_start = false;
    private $time_identifier = null;

    public function __construct() {

        parent::__construct('Collate');
    }

    private function setVariables() {
        global $wgNewManuscriptOptions, $wgArticleUrl, $wgCollationOptions;

        $this->article_url = $wgArticleUrl;

        //if $minimum_manuscripts, $maximum_manuscripts and $max_pages_collection is changed, remember to change the corresponding text in collate.i18n.php
        $this->minimum_manuscripts = $wgCollationOptions['wgmin_collation_pages'];
        $this->maximum_manuscripts = $wgCollationOptions['wgmax_collation_pages'];
        
        $user = $this->getUser();
        $this->user_name = $user->getName();
        //and other variables
    }

    /**
     * Main entry point for the page
     */
    public function execute() {

        $this->setVariables();

        try {
            $this->checkManuscriptDeskPermission();

            if ($this->requestWasPosted()) {
                $this->processRequest();
                return true;
            }

            $this->getForm1();
            return true;
        } catch (Exception $e) {
            $this->handleExceptions($e);
            return false;
        }
    }

    /**
     * Processes the request when a user has submitted the collate form
     */
    private function processRequest() {
        
        $this->checkEditToken();

        if ($this->form1WasPosted()) {
            $this->processForm1();
            return true;
        }

        if ($this->savePageWasRequested()) {
            $this->processSavePageRequest();
            return true;
        }

        if ($this->redirectBackWasRequested()) {
            $this->getForm1();
            return true;
        }

        throw new \Exception('collate-error-request');

        //check if the user tried to save a collation table
        if ($this->save_table) {
            return $this->processSaveTable();
        }
    }
    
    private function temp(){

        $texts = $this->constructTexts();

        //if returned false, one of the posted pages did not exist
        if (!$texts) {
            return $this->showError('collate-error-notexists');
        }

        $text_converter = new textConverter(); //rename this class
        //convert $texts to json, in a format that it can be accepted by Collatex
        $texts_converted = $text_converter->convertJson($texts);

        //send $texts_converted to Collatex, and get the output
        $collatex_output = $text_converter->callCollatex($texts_converted);

        //if the output is an empty string, collatex was not started up or configured properly
        if (!$collatex_output || $collatex_output === "") {
            return $this->showError('collate-error-collatex');
        }

        //construct all the titles, used to display the page titles and collection titles in the table
        $titles_array = $this->constructTitles();

        //construct an URL for the new page
        list($main_title, $new_url) = $this->makeURL($titles_array);

        //time format (Unix Timestamp). This timestamp is used to see how old tempcollate values are
        $time = idate('U');

        $status = $this->prepareTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output);

        if (!$status) {
            return $this->showError('collate-error-database');
        }

        $this->showFirstTable($titles_array, $collatex_output, $time);
    }

    /**
     * This function intializes the $collate_wrapper, clears the tempcollate table, and inserts new data into the tempcollate table 
     */
    private function prepareTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output) {

        $collate_wrapper = new CollateWrapper($this->user_name);

        //delete old entries in the 'tempcollate' table
        $status = $collate_wrapper->clearTempcollate($time);

        if (!$status) {
            return false;
        }

        //store new values in the 'tempcollate' table
        $status = $collate_wrapper->storeTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output);

        if (!$status) {
            return false;
        }

        return true;
    }

    /**
     * This function processes the request when the user wants to save the collation table. Collate data is transferred from the 'tempcollate' table to
     * the 'collations' table, preloaded wikitext is retrieved, a new page is made, and the user is redirected to
     * this page
     */
    private function processSaveTable() {

        $user_name = $this->user_name;
        $collate_wrapper = new CollateWrapper($this->user_name);
        $time_identifier = $this->time_identifier;

        $status = $collate_wrapper->getTempcollate($time_identifier);

        if (!$status) {
            return $this->showError('collate-error-database');
        }

        list($titles_array, $new_url, $main_title, $main_title_lowercase, $collatex_output) = $status;

        $local_url = $this->createNewPage($new_url);

        if (!$local_url) {
            return $this->showError('collate-error-wikipage');
        }

        $status = $collate_wrapper->storeCollations($new_url, $main_title, $main_title_lowercase, $titles_array, $collatex_output);

        if (!$status) {
            return $this->showError('collate-error-database');
        }

        //save data in alphabetnumbersTable   
        $collate_wrapper->storeAlphabetnumbers($main_title_lowercase);

        //redirect the user to the new page
        return $this->getOutput()->redirect($local_url);
    }

    /**
     * This function creates a new wikipage with preloaded wikitext
     */
    private function createNewPage($new_url) {

        $title_object = Title::newFromText($new_url);
        $local_url = $title_object->getLocalURL();

        $context = $this->getContext();

        $article = Article::newFromTitle($title_object, $context);

        //make a new page
        $editor_object = new EditPage($article);
        $content_new = new wikitextcontent('<!--' . $this->msg('collate-newpage') . '-->');
        $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97, false, null, $editor_object->contentFormat);

        if (!$doEditStatus->isOK()) {
            $errors = $doEditStatus->getErrorsArray();
            return false;
        }

        return $local_url;
    }

    /**
     *  This function constructs the $titles_array used by the table, and removes the base url   
     */
    private function constructTitles() {

        $full_manuscripts_url = $this->full_manuscripts_url;
        $posted_hidden_collection_titles = array();

        if (isset($this->collection_hidden_array)) {
            //hidden fields are always sent, and so the correct posted collection titles need to be identified
            foreach ($this->collection_hidden_array as $key => $value) {

                //remove everything except the number
                $number = filter_var($key, FILTER_SANITIZE_NUMBER_INT);

                //see if this collection name appears in $this->collection_array
                $collection_match = 'collection' . $number;

                if (isset($this->collection_array[$collection_match])) {
                    //if it does appear in $this->collection array, add this collection name to $posted_hidden_collection_titles
                    $posted_hidden_collection_titles[$key] = $value;
                }
            }
        }

        //merge these two arrays if collections were also checked
        $titles_array = !empty($posted_hidden_collection_titles) ? array_merge($this->posted_titles_array, $posted_hidden_collection_titles) : $this->posted_titles_array;

        foreach ($titles_array as &$full_url) {

            //remove $full_manuscript_url from each url to get the title
            $full_url = trim(str_replace($full_manuscripts_url, '', $full_url));
        }

        return $titles_array;
    }

    /**
     * This function loops through all the posted collections and titles, and
     * retrieves the text from the corresponding pages 
     * 
     * @return type
     */
    private function constructTexts() {

        //in $texts both single page texts and combined collection texts will be stored 
        $texts = array();

        //collect all single pages
        foreach ($this->posted_titles_array as $file_url) {

            $title_object = Title::newFromText($file_url);

            //if the page does not exist, return false
            if (!$title_object->exists()) {
                return false;
            }

            //get the text
            $single_page_text = $this->getSinglePageText($title_object);

            //check if $single_page_text does not only contain whitespace charachters
            if (ctype_space($single_page_text) || $single_page_text === '') {
                return false;
            }

            //add the text to the array
            $texts[] = $single_page_text;
        }

        if ($this->collection_array) {
            //for collections, collect all single pages of a collection and merge them together
            foreach ($this->collection_array as $collection_name => $url_array) {

                $all_texts_for_one_collection = "";

                //go through all urls of a collection
                foreach ($url_array as $file_url) {

                    $title_object = Title::newFromText($file_url);

                    if (!$title_object->exists()) {
                        return false;
                    }

                    $single_page_text = $this->getSinglePageText($title_object);

                    //add $single_page_text to $single_page_texts
                    $all_texts_for_one_collection .= $single_page_text;
                }

                //check if $all_texts_for_one_collection does not only contain whitespace charachters
                if (ctype_space($all_texts_for_one_collection) || $all_texts_for_one_collection === '') {
                    return false;
                }

                //add the combined texts of one collection to $texts
                $texts[] = $all_texts_for_one_collection;
            }
        }

        return $texts;
    }

    /**
     * This function retrieves the wiki text from a page url
     * 
     * @param type $title_object
     * @return type
     */
    private function getSinglePageText($title_object) {

        $article_object = Wikipage::factory($title_object);
        $raw_text = $article_object->getRawText();

        $filtered_raw_text = $this->filterText($raw_text);

        return $filtered_raw_text;
    }

    /**
     * This function filters out tags, and text in between certain tags. It also trims the text, and adds a single space to the last charachter if needed 
     */
    private function filterText($raw_text) {

        //filter out the following tags, and all text in between the tags
        //pagemetatable tag
        $raw_text = preg_replace('/<pagemetatable>[^<]+<\/pagemetatable>/i', '', $raw_text);

        //del tag
        $raw_text = preg_replace('/<del>[^<]+<\/del>/i', '', $raw_text);

        //note tag
        $raw_text = preg_replace('/<note>[^<]+<\/note>/i', '', $raw_text);

        //filter out any other tags, but keep all text in between the tags
        $raw_text = strip_tags($raw_text);

        //filter out newline charachters and carriage returns, and replace them with a single space
        //$raw_text = preg_replace( '/\r|\n/',' ', $raw_text);
        //trim the text
        $raw_text = trim($raw_text);

        //check if it is possible to get the last charachter of the page
        if (substr($raw_text, -1) !== false) {
            $last_charachter = substr($raw_text, -1);

            if ($last_charachter !== '-') {
                //If the last charachter of the current page is '-', this may indicate that the first word of the next page 
                //is linked to the last word of this page because they form a single word. In other cases, add a space after the last charachter of the current page 
                $raw_text = $raw_text . ' ';
            }
        }

        return $raw_text;
    }

    /**
     * This function prepares the default page, in case no request was posted
     */
    private function getForm1() {
        $collate_wrapper = new CollateWrapper($this->user_name);
        $manuscripts_data = $collate_wrapper->getManuscriptsData();
        $collection_data = $collate_wrapper->getCollectionData();
        $collate_viewer = new CollateViewer($this->getOutput());
        $collate_viewer->showForm1($manuscripts_data, $collection_data);
        return true; 
    }

    /**
     * This function makes a new URL, which will be used when the user saves the current table
     */
    private function makeURL($title_array) {

        $user_name = $this->user_name;
        $imploded_title_array = implode('', $title_array);

        $year_month_day = date('Ymd');
        $hours_minutes_seconds = date('his');

        return array($imploded_title_array, 'Collations:' . $user_name . "/" . $imploded_title_array . "/" . $year_month_day . "/" . $hours_minutes_seconds);
    }

    /**
     * This function fetches the correct error message, and redirects to showDefaultPage()
     * 
     * @param type $type
     */
    private function showError($type) {

        $error_message = $this->msg($type);

        $this->error_message = $error_message;

        return $this->getForm1($this->getOutput());
    }

    /**
     * This function adds html used for the begincollate loader (see ext.begincollate)
     * 
     * Source of the gif: http://preloaders.net/en/circular
     */
    private function addBeginCollateLoader() {

        //shows after submit has been clicked
        $html = "<div id='begincollate-loaderdiv'>";
        $html .= "<img id='begincollate-loadergif' src='/w/extensions/collate/specials/assets/362.gif' style='width: 64px; height: 64px;"
            . " position: relative; left: 50%;'>";
        $html .= "</div>";

        return $html;
    }

    /**
     * This function constructs the HTML collation table, and buttons
     * 
     * @param type $title_array
     * @param type $collatex_output
     */
    private function showFirstTable($title_array, $collatex_output, $time) {

        $out = $this->getOutput();
        $article_url = $this->article_url;

        $redirect_hover_message = $this->msg('collate-redirecthover');
        $redirect_message = $this->msg('collate-redirect');

        $save_hover_message = $this->msg('collate-savehover');
        $save_message = $this->msg('collate-save');

        $html = "
       <div id = 'begincollate-buttons'>
            <form class='begincollate-form-two' action='" . $article_url . "Special:BeginCollate' method='post'> 
            <input type='submit' class='begincollate-submitbutton-two' name ='redirect_to_start' title='$redirect_hover_message'  value='$redirect_message'>
            </form>
            
            <form class='begincollate-form-two' action='" . $article_url . "Special:BeginCollate' method='post'> 
            <input type='submit' class='begincollate-submitbutton-two' name= 'save_current_table' title='$save_hover_message' value='$save_message'> 
            <input type='hidden' name='time' value='$time'>  
            </form>
       </div>";

        $html .= "<p>" . $this->msg('collate-success') . "</p>" . "<p>" . $this->msg('collate-tableread') . " " . $this->msg('collate-savetable') . "</p>";

        $html .= $this->AddBeginCollateLoader();

        $collate = new collate();

        $html .= "<div id='begincollate-tablewrapper'>";

        $html .= $collate->renderTable($title_array, $collatex_output);

        $html .= "</div>";

        return $out->addHTML($html);
    }

    private function handleExceptions($e) {
        
    }

}
