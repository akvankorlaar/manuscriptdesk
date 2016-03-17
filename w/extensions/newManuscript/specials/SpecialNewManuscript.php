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

        $temp_path = $this->checkUploadedImage();

        $save_directory = $this->constructSaveDirectory($posted_manuscript_title);
        $full_path_to_new_file = $save_directory . DIRECTORY_SEPARATOR . $posted_title . '.' . $extension;

        if (file_exists($full_path_to_new_file)) {
            //following error will only trigger if somehow an earlier attempt with this title did not complete (yet). In the case this error triggers, it means
            //that the initial upload exists, but there is no corresponding wiki page (yet), otherwise a $title_error should not be empty.
            // Additional testing needed to see if this error is necessary. 
            throw new \Exception('newmanuscript-error-page');
        }

        if (!file_exists($save_directory)) {
            mkdir($save_directory, 0755, true);
        }

        $upload_succesfull = move_uploaded_file($temp_path, $full_path_to_new_file);

        if (!$upload_succesfull) {
            wfErrorLog($this->msg('newmanuscript-error-upload') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            return $this->showUploadError($this->msg('newmanuscript-error-upload'));
        }

        $prepare_slicer = new SlicerPreparer($posted_title, $full_path_to_new_file, $extension);

        $status = $prepare_slicer->execute();

        if ($status !== true) {
            unlink($full_path_to_new_file);

            if (strpos($status, 'slicer-error-execute') === true) {
                //something went wrong when executing the slicer, so delete all export files, if they exist
                wfErrorLog($status . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
                $prepare_slicer->deleteExportFiles();
                $status = 'slicer-error-execute';
            }

            //get the error message
            $slicer_error_message = $this->msg($status);

            return $this->showUploadError($slicer_error_message);
        }

        $wikipage_status = $this->createNewWikiPage();

        if ($wikipage_status !== true) {
            //something went wrong when creating a new wikipage, so delete all export files, if they exist
            $prepare_slicer->deleteExportFiles();
            wfErrorLog($this->msg($wikipage_status) . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            return $this->showUploadError($this->msg($wikipage_status));
        }

        $new_manuscript_wrapper = new NewManuscriptWrapper();

        $date = date("d-m-Y H:i:s");

        if ($collection_title !== "none") {
            //store information about the collection in the 'collections' table. Only inserts values if collection does not already exist  
            $collectionstable_status = $new_manuscript_wrapper->storeCollections($collection_title, $user_name, $date);
        }

        $new_page_url = $this->constructNewPageUrl($posted_manuscript_title);
        $manuscriptstable_status = $new_manuscript_wrapper->storeManuscripts($posted_title, $collection_title, $user_name, $new_page_url, $date);

        if (!$manuscriptstable_status) {
            //delete all exported files if writing to the database failed, and show an error
            $prepare_slicer->deleteExportFiles();
            wfErrorLog($this->msg('newmanuscript-error-database') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            return $this->showUploadError($this->msg('newmanuscript-error-database'));
        }

        //insert into alphabetnumbersTable
        $new_manuscript_wrapper->storeAlphabetnumbers($posted_title, $collection_title);

        //redirect to the new page
        return $this->getOutput()->redirect($local_url);
    }

    private function checkUploadedImage() {
        $this->checkWhetherFileIsImage();
        $upload_base = $this->getUploadBaseObject();
        $file_name = $this->getFileName($upload_base);
        $extension = $this->getExtensionFromFileName($file_name);
        $temp_path = $this->getTempPath($upload_base);
        $mime_type = $this->getGuessedMimeType($temp_path);

        if ($uploadbase_object::detectScript($temp_path, $mime_type, $extension) === true) {
            throw new \Exception('newmanuscript-error-scripts');
        }
        
        return $temp_path; 
    }

    private function getFileName(UploadBase $upload_base) {
        $title = $upload_base->getTitle();

        if (!isset($title)) {
            throw new \Exception('error-request');
        }

        return $title->getText();
    }

    private function getExtensionFromFileName($file_name) {

        global $wgNewManuscriptOptions;
        $allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];

        if (pathinfo($file_name, PATHINFO_EXTENSION === null)) {
            throw new \Exception('newmanuscript-error-noextension');
        }

        $extension = pathinfo($file_name, PATHINFO_EXTENSION);

        if ($extension === "") {
            throw new \Exception('newmanuscript-error-noextension');
        }

        if (!in_array($extension, $allowed_file_extensions)) {
            throw new \Exception('newmanuscript-error-fileformat');
        }

        return $extension;
    }

    private function getTempPath(UploadBase $upload_base) {
        $temp_path = $upload_base->getTempPath();

        if ($temp_path === '') {
            throw new \Exception('newmanuscript-error-nofile');
        }

        return $temp_path;
    }

    private function getGuessedMimeType($temp_path) {
        
        global $wgNewManuscriptOptions;
        $allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];
        
        $magic = MimeMagic::singleton();
        $mime = strtolower($magic->guessMimeType($temp_path));

        if (!in_array($mime, $allowed_file_extensions)) {
            throw new \Exception('newmanuscript-error-fileformat');
        }

        return $mime;
    }

    private function checkWhetherFileIsImage() {

        if (getimagesize($_FILES["wpUploadFile"]["tmp_name"]) === false) {
            throw new \Exception('newmanuscript-error-noimage');
        }

        return;
    }

    private function constructNewPageUrl($posted_manuscript_title) {
        global $wgNewManuscriptOptions;
        $manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        return $manuscripts_namespace_url . $this->user_name . '/' . $posted_manuscript_title;
    }

    private function checkForCollectionErrors($posted_collection_title) {
        $this->wrapper->checkWhetherCurrentUserIsTheOwnerOfTheCollection($posted_collection_title);
        $this->wrapper->checkCollectionDoesNotExceedMaximumPages($posted_collection_title);
        return;
    }

    private function constructSaveDirectory($posted_manuscript_title) {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        return $wgWebsiteRoot . DIRECTORY_SEPARATOR . $wgNewManuscriptOptions['original_images_dir'] . DIRECTORY_SEPARATOR . $this->user_name . DIRECTORY_SEPARATOR . $posted_manuscript_title;
    }

    private function getUploadBaseObject() {
        global $wgNewManuscriptOptions;
        $max_upload_size = $wgNewManuscriptOptions['max_upload_size'];
        $upload_base = UploadBase::createFromRequest($this->getRequest());

        if (!isset($upload_base)) {
            throw new \Exception('error-request');
        }

        if ($uploadbase_object->getFileSize() > $max_upload_size) {
            throw new \Exception('newmanuscript-error-toolarge');
        }

        return $upload_base;
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
