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
     * Main entry point = ManuscriptDeskBaseSpecials::execute()
     */
    public function __construct() {

        parent::__construct('Collate');
    }

    /**
     * Processes the request when a user has submitted the collate form
     */
    protected function processRequest() {

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
    }

    protected function getForm1($error_message = '') {
        $collate_wrapper = $this->getWrapper();
        $manuscripts_data = $collate_wrapper->getManuscriptsData();
        $collection_data = $collate_wrapper->getCollectionData();
        $collate_viewer = $this->getViewer();
        $collate_viewer->showForm1($manuscripts_data, $collection_data, $error_message);
        return true;
    }

    private function processForm1() {
        $form_data_getter = $this->getFormDataGetter();
        list($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles) = $form_data_getter->getForm1Data();
        $page_titles = $this->getPageTitlesCorrespondingToPostedUrls($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles);
        $page_texts = $this->getTextsFromWikiPages($manuscript_urls, $collection_urls_data);
        $collatex_converter = $this->getCollatexConverter();
        $collatex_output = $collatex_converter->execute($page_texts);
        $imploded_page_titles = $this->createImplodedPageTitles($page_titles);
        $new_url = $this->makeUrlForNewPage($imploded_page_titles);
        $time = idate('U'); //time format (Unix Timestamp). This timestamp is used to see how old tempcollate values are
        $this->updateDatabase($page_titles, $imploded_page_titles, $new_url, $time, $collatex_output);
        $collate_viewer = $this->getViewer();
        $collate_viewer->showCollatexOutput($page_titles, $collatex_output, $time);
        return true;
    }

    /**
     * This function processes the request when the user wants to save the collation table. Collate data is transferred from the 'tempcollate' table to
     * the 'collations' table, a new page is made, and the user is redirected to this page
     */
    private function processSavePageRequest() {
        $form_data_getter = $this->getFormDataGetter();
        $time_identifier = $form_data_getter->getSavePageData();
        $collate_wrapper = $this->getWrapper();
        list($new_url, $main_title, $main_title_lowercase, $page_titles, $collatex_output) = $collate_wrapper->getSavePageData($time_identifier);
        $collate_wrapper->storeCollations($new_url, $main_title, $main_title_lowercase, $page_titles, $collatex_output);
        $collate_wrapper->incrementAlphabetNumbers($main_title_lowercase, 'AllCollations');
        $local_url = $this->createNewWikiPage($new_url);
        return $this->getOutput()->redirect($local_url);
    }

    /**
     * $titles are posted inside of hidden fields, which are always sent, and so the $titles corresponding to the $urls need to be found
     */
    private function getPageTitlesCorrespondingToPostedUrls($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles) {
        $corresponding_manuscript_titles = $this->getCorrespondingTitles($manuscript_urls, $manuscript_titles, 'manuscript_urls');
        $corresponding_collection_titles = $this->getCorrespondingTitles($collection_urls_data, $collection_titles, 'collection_urls');
        return array_merge($corresponding_manuscript_titles, $corresponding_collection_titles);
    }

    private function getCorrespondingTitles(array $urls, array $titles, $base_match = '') {

        $corresponding_titles = array();
        foreach ($titles as $key => $title) {
            //remove everything except the $number_identifier
            $number_identifier = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
            $match = $base_match . $number_identifier;

            //see if this $match appears in $urls
            if (isset($urls[$match])) {
                //if it does appear, add the current $title to $corresponding_titles 
                $corresponding_titles[$key] = $title;
            }
        }

        return $corresponding_titles;
    }

    private function getTextsFromWikiPages(array $manuscript_urls, array $collection_data) {
        $page_texts_manuscripts = $this->getPageTextsForSingleManuscriptPages($manuscript_urls);
        $page_texts_collections = $this->getPageTextsForCollections($collection_data);
        return array_merge($page_texts_manuscripts, $page_texts_collections);
    }

    /**
     * This function intializes the $collate_wrapper, clears the tempcollate table, and inserts new data into the tempcollate table 
     */
    private function updateDatabase($titles_array, $main_title, $new_url, $time, $collatex_output) {
        $collate_wrapper = new CollateWrapper($this->user_name);
        $collate_wrapper->clearOldCollatexOutput($time);
        $collate_wrapper->storeTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output);
        return true;
    }

    private function getPageTextsForSingleManuscriptPages(array $manuscript_urls) {

        $texts = array();
        foreach ($manuscript_urls as $single_manuscript_url) {

            $title = $this->constructTitleObjectFromUrl($single_manuscript_url);
            $single_page_text = $this->getFilteredSinglePageText($title);
            $this->checkIfTextIsNotOnlyWhitespace($single_page_text);
            $texts[] = $single_page_text;
            $test= strlen($single_page_text);
            $a = 5;
        }

        return $texts;
    }

    private function getPageTextsForCollections(array $collection_data) {

        $texts = array();    
        foreach ($collection_data as $single_collection_urls) {

            $all_texts_for_one_collection = "";

            foreach ($single_collection_urls as $single_manuscript_url) {
                $title = $this->constructTitleObjectFromUrl($single_manuscript_url);
                $single_page_text = $this->getFilteredSinglePageText($title);
                $all_texts_for_one_collection .= $single_page_text;
            }

            $this->checkIfTextIsNotOnlyWhitespace($all_texts_for_one_collection);
            $texts[] = $all_texts_for_one_collection;
        }

        return $texts;
    }

    private function constructTitleObjectFromUrl($single_manuscript_url = '') {
        $title = Title::newFromText($single_manuscript_url);

        if (!$title->exists()) {
            throw new \Exception('collation-error-notexts');
        }

        return $title;
    }

    private function checkIfTextIsNotOnlyWhitespace($text = '') {
        if (ctype_space($text) || $text === '') {
            throw new Exception('collation-error-notexts');
        }
    }

    private function createImplodedPageTitles(array $page_titles) {
        return implode('', $page_titles);
    }

    /**
     * This function makes a new URL, which will be used when the user saves the current table
     */
    private function makeUrlForNewPage($imploded_page_titles = '') {
        $user_name = $this->user_name;
        $year_month_day = date('Ymd');
        $hours_minutes_seconds = date('his');
        return 'Collations:' . $user_name . "/" . $imploded_page_titles . "/" . $year_month_day . "/" . $hours_minutes_seconds;
    }
    
    protected function getViewer(){
        return new CollateViewer($this->getOutput());
    }
    
    protected function getWrapper(){
        return new CollateWrapper($this->user_name);
    }
    
    protected function getFormDataGetter(){
       return new CollateFormDataGetter($this->getRequest(), new ManuscriptDeskBaseValidator());
    }
    
    protected function getCollatexConverter(){
        return new CollatexConverter();
    }

}
