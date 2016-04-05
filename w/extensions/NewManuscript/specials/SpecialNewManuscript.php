<?php

/**
 * This file is part of the NewManuscript extension
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
class SpecialNewManuscript extends ManuscriptDeskBaseSpecials {

    private $paths;
    private $posted_collection_title;
    private $posted_manuscript_title;

    public function __construct() {
        parent::__construct('NewManuscript');
    }

    protected function getDefaultPage($error_message = '', $collection_title = '') {
        $this->checkWhetherUserHasUploadedTooManyManuscripts();
        $collections_current_user = $this->wrapper->getCollectionsCurrentUser();
        $this->viewer->showDefaultpage($error_message, $collections_current_user, $collection_title);
    }

    /**
     * This function checks whether the user has reached the maximum of allowed uploads
     */
    private function checkWhetherUserHasUploadedTooManyManuscripts() {
        global $wgNewManuscriptOptions;
        $max_manuscripts = $wgNewManuscriptOptions['max_manuscripts'];
        $number_of_uploads = $this->wrapper->getNumberOfUploadsForCurrentUser();

        if ($number_of_uploads > $max_manuscripts) {
            throw new \Exception('newmanuscript-maxreached');
        }
        else {
            return;
        }
    }

    protected function processRequest() {

        if ($this->request_processor->addNewPagePosted()) {
            $collection_title = $this->request_processor->getCollectionTitle();
            return $this->getDefaultPage('', $collection_title);
        }
        else {
            return $this->processUploadedNewManuscript();
        }
    }

    private function processUploadedNewManuscript() {

        list($posted_manuscript_title, $posted_collection_title) = $this->request_processor->loadUploadFormData();
        $this->posted_manuscript_title = $posted_manuscript_title;
        $this->posted_collection_title = $posted_collection_title;

        if ($posted_collection_title !== 'none') {
            $this->checkForCollectionErrors();
        }

        $image_validator = new NewManuscriptImageValidator($this->getRequest());
        list($temp_path, $extension) = $image_validator->getAndCheckUploadedImageData();
        $this->setPaths($extension);
        $this->paths->moveUploadToInitialUploadDir($temp_path);
        $this->prepareAndExecuteSlicer();
        $new_page_url = $this->paths->getPartialUrl();
        $this->updateDatabase($new_page_url);
        $local_url = $this->createNewWikiPage($new_page_url, 'This page has not been transcribed yet.');
        $this->getOutput()->redirect($local_url);
        return true;
    }

    private function checkForCollectionErrors() {
        $posted_collection_title = $this->posted_collection_title;
        $this->wrapper->checkWhetherCurrentUserIsTheOwnerOfTheCollection($posted_collection_title);
        $this->wrapper->checkCollectionDoesNotExceedMaximumPages($posted_collection_title);
        return;
    }

    private function setPaths($extension) {
        $posted_manuscript_title = $this->posted_manuscript_title;
        $this->paths = $paths = new NewManuscriptPaths($this->user_name, $posted_manuscript_title, $extension);
        $paths->setInitialUploadFullPath();
        $paths->setExportPaths();

        $full_export_path = $paths->getFullExportPath();
        if (is_dir($full_export_path)) {
            throw new \Exception('error-request');
        }

        $paths->setPartialUrl();
        return;
    }

    private function prepareAndExecuteSlicer() {
        $slicer_executer = new SlicerExecuter($this->paths);
        return $slicer_executer->execute();
    }

    private function updateDatabase($new_page_url) {
        $posted_collection_title = $this->posted_collection_title;
        $posted_manuscript_title = $this->posted_manuscript_title;
        $date = date("d-m-Y H:i:s");

        if ($posted_collection_title !== "none") {
            $this->wrapper->storeCollections($posted_collection_title, $this->user_name, $date);
        }

        $this->wrapper->storeManuscripts($posted_manuscript_title, $posted_collection_title, $this->user_name, $new_page_url, $date);
        $alphabetnumbers_context = $this->wrapper->determineAlphabetNumbersContextFromCollectionTitle($posted_collection_title);
        $this->wrapper->incrementAlphabetNumbers(strtolower($posted_manuscript_title), $alphabetnumbers_context);
        return;
    }

    protected function handleExceptions(Exception $exception_error) {

        global $wgWebsiteRoot;
        $viewer = $this->setViewer();
        $error_identifier = $exception_error->getMessage();
        $error_message = $this->constructErrorMessage($exception_error, $error_identifier);

        if ($error_identifier === 'error-nopermission' || $error_identifier === 'newmanuscript-maxreached') {
            return $viewer->showSimpleErrorMessage($error_message);
        }

        if ($error_identifier === 'slicer-error-execute' || $error_identifier === 'error-newpage' || $error_identifier === 'error-database-manuscripts') {
            $this->deleteAllData();
            wfErrorLog($error_identifier . "\r\n", $wgWebsiteRoot . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
        }

        return $this->getDefaultPage($error_message);
    }

    private function deleteAllData() {
        $deleter = new ManuscriptDeskDeleter(new ManuscriptDeskDeleteWrapper(), $this->paths, $this->posted_collection_title);
        return $deleter->excute();
    }

    protected function setRequestProcessor() {
        
        if(isset($this->request_processor)){
            return;
        }
        
        return $this->request_processor = new NewManuscriptRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator);
    }

    protected function setViewer() {
        
        if(isset($this->viewer)){
            return;
        }
        
        return $this->viewer = new NewManuscriptViewer($this->getOutput());
    }

    protected function setWrapper() {
        
        if(isset($this->wrapper)){
            return;
        }
        
        return $this->wrapper = new NewManuscriptWrapper($this->user_name);
    }

    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
    static function showUploadError($form_data) {
        return false;
    }

}
