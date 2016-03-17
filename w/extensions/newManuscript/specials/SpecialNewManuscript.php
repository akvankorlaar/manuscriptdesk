<?php

/**
 * This file is part of the newManuscript extension
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

    private $slicer_executer;
    private $paths;

    public function __construct() {
        parent::__construct('NewManuscript');
    }

    protected function getDefaultPage($error_message = '') {
        $this->checkWhetherUserHasUploadedTooManyManuscripts();
        $collections_current_user = $this->wrapper->getCollectionsCurrentUser();
        $this->viewer->showDefaultpage($error_message, $collections_current_user);
    }

    /**
     * This function checks whether the user has reached the maximum of allowed uploads
     */
    private function checkWhetherUserHasUploadedTooManyManuscripts() {
        $max_manuscripts = $wgNewManuscriptOptions['max_manuscripts'];
        $number_of_uploads = $this->wrapper->getNumberOfUploadsForCurrentUser();

        if ($number_of_uploads <= $max_manuscripts) {
            throw new \Exception('newmanuscript-maxreached');
        }
        else {
            return;
        }
    }

    protected function processRequest() {
        list($posted_manuscript_title, $posted_collection_title, $selected_collection) = $this->request_processor->loadUploadFormData();

        if ($posted_collection_title !== 'none') {
            $this->checkForCollectionErrors($posted_collection_title);
        }

        $image_validator = new NewManuscriptImageValidator($this->getRequest());
        list($temp_path, $extension) = $image_validator->getAndCheckUploadedImageData();
        $this->setPaths($posted_collection_title, $extension);
        $this->paths->moveUploadToInitialUploadDir($temp_path);
        $this->prepareAndExecuteSlicer();
        $local_url = $this->createNewWikiPage($this->paths->getNewPageUrl());
        $this->updateDatabase();
        $this->getOutput()->redirect($local_url);
        return true;
    }

    private function checkForCollectionErrors($posted_collection_title) {
        $this->wrapper->checkWhetherCurrentUserIsTheOwnerOfTheCollection($posted_collection_title);
        $this->wrapper->checkCollectionDoesNotExceedMaximumPages($posted_collection_title);
        return;
    }

    private function setPaths($posted_collection_title, $extension) {
        $this->paths = $paths = new NewManuscriptPaths($this->user_name, $posted_collection_title, $extension);
        $paths->setInitialUploadFullPath();
        $paths->setBaseExportPath();
        $paths->setUserExportPath();
        $paths->setFullExportPath();
        $paths->setNewPageUrl();
        return;
    }

    private function prepareAndExecuteSlicer() {
        $this->slicer_executer = $slicer_executer = new SlicerExecuter($this->paths);
        return $slicer_executer->execute();
    }

    private function updateDatabase($posted_collection_title) {

        $user_name = $this->user_name; 
        $date = date("d-m-Y H:i:s");

        if ($posted_collection_title !== "none") {
           $this->wrapper->storeCollections($posted_collection_title, $user_name, $date);
        }

        $this->wrapper->storeManuscripts($posted_manuscript_title, $posted_collection_title, $user_name, $new_page_url, $date);

        if (!$manuscriptstable_status) {
            //delete all exported files if writing to the database failed, and show an error
            $slicer_preparer->deleteExportFiles();
            wfErrorLog($this->msg('newmanuscript-error-database') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            return $this->showUploadError($this->msg('newmanuscript-error-database'));
        }

        $this->wrapper->storeAlphabetnumbers($posted_manuscript_title, $posted_collection_title);
    }


    protected function tempHandleExceptions() {
        if ($status !== true) {
            unlink($initial_upload_full_path);

            if (strpos($status, 'slicer-error-execute') === true) {
                //something went wrong when executing the slicer, so delete all export files, if they exist
                wfErrorLog($status . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
                $slicer_executer->deleteExportFiles();
                $status = 'slicer-error-execute';
            }

            $slicer_error_message = $this->msg($status);

            return $this->showUploadError($slicer_error_message);
        }


        if ($wikipage_status !== true) {
            //something went wrong when creating a new wikipage, so delete all export files, if they exist
            $slicer_preparer->deleteExportFiles();
            wfErrorLog($this->msg($wikipage_status) . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            return $this->showUploadError($this->msg($wikipage_status));
        }
    }

    protected function getRequestprocessor() {
        return new NewManuscriptRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator);
    }

    protected function getViewer() {
        return new NewManuscriptViewer($this->getOutput());
    }

    protected function getWrapper() {
        return new NewManuscriptWrapper($this->user_name);
    }

    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
    static function showUploadError($form_data) {
        return false;
    }

}
