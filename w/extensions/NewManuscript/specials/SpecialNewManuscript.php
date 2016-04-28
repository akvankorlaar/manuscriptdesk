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
    private $image_validator;
    private $slicer_executer;
    private $posted_collection_title;
    private $posted_manuscript_title;
    private $extension;

    public function __construct() {
        parent::__construct('NewManuscript');
    }

    protected function getDefaultPage($error_message = '', $collection_title = '') {
        $this->checkWhetherUserHasUploadedTooManyManuscripts();
        $collections_current_user = $this->wrapper->getCollectionsCurrentUser();
        $this->viewer->showDefaultpage($error_message, $collections_current_user, $collection_title);
        return;
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

        $this->setImageValidator();
        
        $image_validator = $this->image_validator;
        list($temp_path, $extension) = $image_validator->getAndCheckUploadedImageData();
        $this->extension = $extension;

        $this->setPaths();    
        $this->setPathsData();
        $this->paths->moveUploadToInitialUploadDir($temp_path);

        $this->prepareAndExecuteSlicer();
        
        $new_page_url = $this->paths->getPartialUrl();
        $this->updateDatabase($new_page_url);
        $local_url = $this->createNewWikiPage($new_page_url, 'This page has not been transcribed yet.');
        $this->getOutput()->redirect($local_url);
        return;
    }

    private function checkForCollectionErrors() {
        $posted_collection_title = $this->posted_collection_title;
        $this->wrapper->checkWhetherCurrentUserIsTheOwnerOfTheCollection($posted_collection_title);
        $this->wrapper->checkCollectionDoesNotExceedMaximumPages($posted_collection_title);
        return;
    }

    private function setPathsData() {
        $paths = $this->paths;
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
        $this->setSlicerExecuter();
        $slicer_executer = $this->slicer_executer;
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
        $alphabetnumbers_context = $this->wrapper->getAlphabetNumbersWrapper()->determineAlphabetNumbersContextFromCollectionTitle($posted_collection_title);
        $this->wrapper->getAlphabetNumbersWrapper()->modifyAlphabetNumbersSingleValue(strtolower($posted_manuscript_title), $alphabetnumbers_context, 'add');
        return;
    }

    protected function handleExceptions(Exception $exception_error) {
        global $wgWebsiteRoot;
        $this->setViewer();
        $viewer = $this->viewer;
        $error_identifier = $exception_error->getMessage();
        $error_message = $this->constructErrorMessage($exception_error, $error_identifier);
        $this->error_identifier = $error_identifier;

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
        $deleter = ObjectRegistry::getInstance()->getManuscriptDeskDeleter($this->posted_collection_title);
        return $deleter->deleteManuscriptPage();
    }

    public function setPaths() {

        if (isset($this->paths)) {
            return;
        }
        
        return $this->paths = ObjectRegistry::getInstance()->getNewManuscriptPaths($this->user_name, $this->posted_manuscript_title, $this->extension);;
    }

    public function setImageValidator() {

        if (isset($this->image_validator)) {
            return;
        }
            
        return $this->image_validator = ObjectRegistry::getInstance()->getImageValidator($this->getRequest());
    }

    public function setSlicerExecuter() {

        if (isset($this->slicer_executer)) {
            return;
        }
        
        return $this->slicer_executer = ObjectRegistry::getInstance()->getSlicerExecuter();
    }

    public function setRequestProcessor() {

        if (isset($this->request_processor)) {
            return;
        }

        return $this->request_processor = ObjectRegistry::getInstance()->getNewManuscriptRequestProcessor($this->getRequest());
    }

    public function setViewer() {

        if (isset($this->viewer)) {
            return;
        }

        return $this->viewer = ObjectRegistry::getInstance()->getNewManuscriptViewer($this->getOutput());
    }

    public function setWrapper($object = null) {

        if (isset($this->wrapper)) {
            return;
        }
        
        $wrapper = ObjectRegistry::getInstance()->getNewManuscriptWrapper();
        $wrapper->setUserName($this->user_name);
        return $this->wrapper = $wrapper;
    }

    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
    static function showUploadError($form_data) {
        return false;
    }

}
