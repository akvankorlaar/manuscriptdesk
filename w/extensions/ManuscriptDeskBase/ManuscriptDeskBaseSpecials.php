<?php

/**
 * This file is part of the collate extension
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
 * 
 * Idea: Refractor code and use interfaces. For example, there is some duplication in the execute and handleErrors methods
 */
class ManuscriptDeskBaseSpecials extends SpecialPage {

    protected $user_name;

    public function __construct($page_name) {
        parent::__construct($page_name);
    }

    protected function setVariables() {
        $user = $this->getUser();
        $this->user_name = $user->getName();
    }

    /**
     * Main entry point for Special Pages in the Manuscript Desk
     */
    public function execute() {

        try {
            $this->setVariables();
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
     * This function checks if the edit token was posted
     */
    protected function tokenWasPosted() {
        $edit_token = $this->getEditToken();
        if ($edit_token === '') {
            return false;
        }

        return true;
    }

    /**
     * This function gets the edit token
     */
    protected function getEditToken() {
        $request = $this->getRequest();
        return $request->getText('wpEditToken');
    }

    /**
     * This function checks the edit token
     */
    protected function checkEditToken() {
        $edit_token = $this->getEditToken();
        if ($this->getUser()->matchEditToken($edit_token) === false) {
            throw new \Exception('error-edittoken');
        }

        return true;
    }

    /**
     * This function checks if the user has the appropriate permissions
     */
    protected function checkManuscriptDeskPermission() {
        $out = $this->getOutput();
        $user_object = $out->getUser();

        if (!in_array('ManuscriptEditors', $user_object->getGroups())) {
            throw new \Exception('error-nopermission');
        }

        return true;
    }

    protected function requestWasPosted() {
        $request = $this->getRequest();

        if (!$request->wasPosted()) {
            return false;
        }

        return true;
    }

    protected function getAllTextsForOneCollection(array $single_collection_data) {
        $all_texts_for_one_collection = "";
        foreach ($single_collection_data as $index => $single_manuscript_url) {
            if ($index !== 'collection_name') {
                $title = $this->constructTitleObjectFromUrl($single_manuscript_url);
                $single_page_text = $this->getFilteredSinglePageText($title);
                $all_texts_for_one_collection .= $single_page_text;
            }
        }

        $this->checkIfTextIsNotOnlyWhitespace($all_texts_for_one_collection);
        return $all_texts_for_one_collection;
    }

    protected function getFilteredSinglePageText(Title $title) {
        $wikipage = Wikipage::factory($title);
        $raw_text = $wikipage->getText();
        $filtered_raw_text = $this->filterText($raw_text);
        return $filtered_raw_text;
    }

    /**
     * This function filters out tags, and text in between certain tags. It also trims the text, and adds a single space to the last charachter if needed 
     */
    protected function filterText($raw_text) {

        //filter out the following tags, and all text in between the tags
        //pagemetatable tag
        $raw_text = preg_replace('/<pagemetatable>[^<]+<\/pagemetatable>/i', '', $raw_text);

        //del tag
        $raw_text = preg_replace('/<del>[^<]+<\/del>/i', '', $raw_text);

        //note tag
        $raw_text = preg_replace('/<note>[^<]+<\/note>/i', '', $raw_text);

        //filter out any other tags, but keep all text in between the tags
        $raw_text = strip_tags($raw_text);

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

    protected function createNewWikiPage($new_url = '') {

        $title_object = Title::newFromText($new_url);
        $local_url = $title_object->getLocalURL();
        $context = $this->getContext();
        $article = Article::newFromTitle($title_object, $context);

        $editor_object = new EditPage($article);
        $content_new = new wikitextcontent('<!--' . $this->msg('manuscriptdesk-newpage') . '-->');
        $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97, false, null, $editor_object->contentFormat);

        if (!$doEditStatus->isOK()) {
            throw new \Exception('error-newpage');
            //$errors = $doEditStatus->getErrorsArray();
        }

        return $local_url;
    }

    protected function form1WasPosted() {
        $request = $this->getRequest();
        if ($request->getText('form1Posted') !== '') {
            return true;
        }

        return false;
    }

    protected function redirectBackWasRequested() {
        $request = $this->getRequest();
        if ($request->getText('redirect') !== '') {
            return true;
        }

        return false;
    }

    protected function savePageWasRequested() {
        $request = $this->getRequest();
        if ($request->getText('save_current_page') === '') {
            return false;
        }

        return true;
    }

    protected function constructTitleObjectFromUrl($single_manuscript_url = '') {
        $title = Title::newFromText($single_manuscript_url);

        if (!$title->exists()) {
            throw new \Exception('error-titledoesnotexist');
        }

        return $title;
    }

    protected function checkIfTextIsNotOnlyWhitespace($text = '') {
        if (ctype_space($text) || $text === '') {
            throw new Exception('error-notextonwikipage');
        }
    }

    protected function handleExceptions(Exception $exception_error) {

        $viewer = $this->getViewer();
        $error_identifier = $exception_error->getMessage();
        $error_message = $this->constructErrorMessage($exception_error, $error_identifier);

        if ($error_identifier === 'error-nopermission') {
            return $viewer->showNoPermissionError($error_message);
        }

        if ($error_identifier === 'error-fewuploads') {
            return $viewer->showFewUploadsError($error_message);
        }

        if ($this instanceof SpecialStylometricAnalysis) {
            if ($this->form_type === 'Form2' && isset($this->collection_data) && isset($this->collection_name_data)) {
                return $viewer->showForm2($this->collection_data, $this->collection_name_data, $this->getContext(), $error_message);
            }
        }

        return $this->getForm1($error_message);
    }

    private function constructErrorMessage(Exception $exception_error, $error_identifier) {

        global $wgShowExceptionDetails;

        if ($wgShowExceptionDetails === true) {
            $error_line = $exception_error->getLine();
            $error_file = $exception_error->getFile();
            $error_message = $this->msg($error_identifier) . ' ' . $error_line . ' ' . $error_file;
        }
        else {
            $error_message = $this->msg($error_identifier);
        }

        return $error_message;
    }

}
