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
class SpecialUserPage extends ManuscriptDeskBaseSpecials {

    private $form_type = 'default';
    private $manuscript_old_title;
    private $manuscript_new_title; 
    private $manuscript_url_old_title;
    private $new_page_partial_url; 

    public function __construct() {
        parent::__construct('UserPage');
    }

    /**
     * Process all requests
     */
    protected function processRequest() {

        $request_processor = $this->request_processor;
        $request_processor->checkEditToken($this->getUser());

        if ($request_processor->defaultPageWasPosted()) {
            $this->processDefaultPage();
            return true;
        }

        if ($request_processor->singleCollectionPosted()) {
            $this->getSingleCollectionPage();
            return true;
        }

        if ($request_processor->EditMetadataPosted()) {
            $this->getEditMetadataForm();
            return true;
        }

        if ($request_processor->saveCollectionMetadataPosted()) {
            $this->processSaveCollectionMetadata();
            return true;
        }

        if ($request_processor->editSinglePageCollectionPosted()) {
            $this->getEditSinglePageCollectionForm();
            return true;
        }

        if ($request_processor->saveNewPageTitleCollectionPosted()) {
            $this->processNewPageTitleCollection();
            return true;
        }

        throw new \Exception('error-request');
    }

    protected function getDefaultPage($error_message = '') {
        $user_is_a_sysop = $this->checkWhetherUserIsASysop();
        $this->viewer = new UserPageDefaultViewer($this->getOutput());
        $this->viewer->showDefaultPage($error_message, $this->user_name, $user_is_a_sysop);
    }

    private function checkWhetherUserIsASysop() {
        $user = $this->getUser();
        if (!in_array('sysop', $user->getGroups())) {
            return false;
        }

        return true;
    }

    private function processDefaultPage() {
        list($button_name, $offset) = $this->request_processor->getDefaultPageData();
        $this->setWrapperAndViewer($button_name);
        list($page_titles, $next_offset) = $this->wrapper->getData($offset);

        if (empty($page_titles)) {
            $this->viewer->showEmptyPageTitlesError($button_name);
            return true;
        }

        $this->viewer->showPage($button_name, $page_titles, $offset, $next_offset);
        return true;
    }

    private function getSingleCollectionPage() {
        $collection_title = $this->request_processor->getCollectionTitle();
        $this->setWrapperAndViewer('view_collections_posted');
        $single_collection_data = $this->wrapper->getSingleCollectionData($collection_title);
        return $this->viewer->showSingleCollectionData($collection_title, $single_collection_data);
    }

    private function getEditMetadataForm($error_message = '') {
        $collection_title = $this->request_processor->getCollectionTitle();
        $link_back_to_manuscript_page = $this->request_processor->getLinkBackToManuscriptPage();
        $this->setWrapperAndViewer('view_collections_posted');
        $collection_metadata = $this->wrapper->getSingleCollectionMetadata($collection_title);
        return $this->viewer->showEditCollectionMetadata($collection_title, $collection_metadata, $link_back_to_manuscript_page, $error_message);
    }

    private function processSaveCollectionMetadata() {
        $this->form_type = 'edit_metadata';
        $saved_metadata = $this->request_processor->getAndValidateSavedCollectionMetadata();
        $collection_title = $this->request_processor->getCollectionTitle();
        $this->setWrapperAndViewer('view_collections_posted');
        $this->wrapper->updateCollectionsMetadata($saved_metadata, $collection_title);
        $link_back_to_manuscript_page = $this->request_processor->getLinkBackToManuscriptPage();

        if (!empty($link_back_to_manuscript_page)) {
            return $this->viewer->showRedirectBackToManuscriptPageAfterEditMetadata($link_back_to_manuscript_page);
        }

        $single_collection_data = $this->wrapper->getSingleCollectionData($collection_title);
        return $this->viewer->showSingleCollectionData($collection_title, $single_collection_data);
    }

    private function getEditSinglePageCollectionForm($error_message = '') {
        $this->setWrapperAndViewer('view_collections_posted');
        $collection_title = $this->request_processor->getCollectionTitle();
        $counter = $this->request_processor->getEditSinglePageCounter();
        list($manuscript_old_title, $manuscript_url_old_title) = $this->request_processor->getEditSinglePageCollectionData($counter);
        $this->viewer->showEditPageSingleCollectionForm($error_message, $collection_title, $manuscript_old_title, $manuscript_url_old_title);
    }

    /**
     * This function processes the edit when submitting a new manuscript page title
     */
    private function processNewPageTitleCollection() {
        $this->form_type = 'edit_single_page';
        $this->setWrapperAndViewer('view_collections_posted');

        list($manuscript_old_title, $manuscript_url_old_title) = $this->request_processor->getEditSinglePageCollectionData();
        $manuscript_new_title = $this->request_processor->getManuscriptNewTitleData();
        
        //if the new title and the old title are equal, do nothing and return 
        if ($manuscript_new_title === $manuscript_old_title) {
            return $this->getSingleCollectionPage();
        }
        
        $this->manuscript_old_title = $manuscript_old_title;
        $this->manuscript_new_title = $manuscript_new_title; 
        $this->manuscript_url_old_title = $manuscript_url_old_title;
        $new_page_partial_url = $this->new_page_partial_url = $this->createNewPagePartialUrl($manuscript_new_title);
        $this->renameFilePaths($manuscript_old_title, $manuscript_new_title);
        $this->updateDatabase($manuscript_new_title, $manuscript_url_old_title, $new_page_partial_url);
        $this->createNewWikiPageWithOldPageText($manuscript_url_old_title, $new_page_partial_url);
        $this->deleteOldWikiPage($manuscript_url_old_title);
        return $this->getSingleCollectionPage();
    }

    private function createNewPagePartialUrl($manuscript_new_title) {
        global $wgNewManuscriptOptions;
        $user_name = $this->user_name;
        $manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
        return trim($manuscripts_namespace_url . $user_name . '/' . $manuscript_new_title);
    }

    private function updateDatabase($manuscript_new_title, $manuscript_url_old_title, $new_page_partial_url) {
        $status = $this->wrapper->updateManuscriptsTable($manuscript_new_title, $new_page_partial_url, $manuscript_url_old_title);
        return; 
    }

    private function renameFilePaths($manuscript_old_title, $manuscript_new_title) {
        list($old_zoomimages_path, $new_zoomimages_path) = $this->createOldAndNewZoomimagesPaths($manuscript_old_title, $manuscript_new_title);
        list($old_original_images_path, $new_original_images_path) = $this->createOldAndNewOriginalImagesPaths($manuscript_old_title, $manuscript_new_title);
        rename($old_zoomimages_path, $new_zoomimages_path);
        rename($old_original_images_path, $new_original_images_path);
        return true;
    }

    private function createOldAndNewZoomimagesPaths($manuscript_old_title, $manuscript_new_title) {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $zoomimages_dirname = $wgNewManuscriptOptions['zoomimages_root_dir'];
        $user_name = $this->user_name;

        $old_zoomimages_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $zoomimages_dirname . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_old_title;
        $new_zoomimages_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $zoomimages_dirname . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_new_title;

        if (!is_dir($old_zoomimages_path)) {
            throw new \Exception('error-internal');
        }

        return array($old_zoomimages_path, $new_zoomimages_path);
    }

    private function createOldAndNewOriginalImagesPaths($manuscript_old_title, $manuscript_new_title) {

        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $original_images_dir = $wgNewManuscriptOptions['original_images_dir'];
        $user_name = $this->user_name;

        $old_original_images_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $original_images_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_old_title;
        $new_original_images_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $original_images_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_new_title;

        if (!is_dir($old_original_images_path)) {
            throw new \Exception('error-internal');
        }

        return array($old_original_images_path, $new_original_images_path);
    }

    private function createNewWikiPageWithOldPageText($manuscript_url_old_title, $new_page_url) {
        $text_processor = new ManuscriptDeskBaseTextProcessor();
        $old_page_text = $text_processor->getSinglePageText($manuscript_url_old_title);
        $this->createNewWikiPage($new_page_url, $old_page_text);
        return true;
    }

    private function deleteOldWikiPage($manuscript_url_old_title) {
        $page_id = $this->wrapper->getPageId($manuscript_url_old_title);
        return $this->wrapper->deleteOldPage($page_id);
    }

    protected function handleExceptions(Exception $exception_error) {

        //Todo: Exception when renaming file name should roll back changes (database/file/wikipage renames)
        $error_identifier = $exception_error->getMessage();
        $error_message = $this->constructErrorMessage($exception_error, $error_identifier);

        switch ($this->form_type) {
            case 'default':
                return $this->getDefaultPage($error_message);
                break;
            case 'edit_metadata':
                return $this->getEditMetadataForm($error_message);
                break;
            case 'edit_single_page':
                if($error_identifier === 'error-database-update'){
                    $this->renameFilePaths($this->manuscript_new_title, $this->manuscript_old_title);                
                }elseif($error_identifier === 'error-newpage' || $error_identifier === 'error-titledoesnotexist'){
                      $this->renameFilePaths($this->manuscript_new_title, $this->manuscript_old_title);                
                      $this->updateDatabase($this->manuscript_old_title, $this->new_page_partial_url, $this->manuscript_url_old_title);
                }
                
                return $this->getEditSinglePageCollectionForm($error_message);
        }

        return true;
    }

    protected function setViewer() {
        //empty because viewer has to be determined at runtime
        return;
    }

    protected function setWrapper() {
        //empty because wrapper has to be determined at runtime   
        return;
    }

    private function setWrapperAndViewer($button_name) {

        if (isset($this->wrapper) || isset($this->viewer)) {
            return;
        }

        switch ($button_name) {
            case 'view_manuscripts_posted':
                $this->wrapper = new SingleManuscriptPagesWrapper($this->user_name);
                $this->viewer = new UserPageManuscriptsViewer($this->getOutput(), $this->user_name);
                break;
            case 'view_collations_posted':
                $this->wrapper = new AllCollationsWrapper($this->user_name);
                $this->viewer = new UserPageCollationsViewer($this->getOutput(), $this->user_name);
                break;
            case 'view_collections_posted':
                $this->wrapper = new AllCollectionsWrapper($this->user_name);
                $this->viewer = new UserPageCollectionsViewer($this->getOutput(), $this->user_name);
                break;
        }

        if (!isset($this->wrapper) || !isset($this->viewer)) {
            throw new \Exception('error-request');
        }

        return;
    }

    protected function setRequestProcessor() {

        if (isset($this->request_processor)) {
            return;
        }

        return $this->request_processor = new UserPageRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator());
    }

    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
    static function processInput($form_data) {
        return false;
    }

}
