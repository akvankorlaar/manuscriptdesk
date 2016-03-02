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

    /**
     * SpecialuserPage. Organises all content created by a user
     */
//    public $max_length = 50;
//    private $button_name; //value of the button the user clicked on 
//    
//    private $selected_collection;
//    private $textfield_array = array();
//    private $linkback = null;
//    private $manuscript_old_title;
//    private $manuscript_url_old_title;
//    private $manuscript_new_title;

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
            $this->processSingleCollection();
            return true;
        }

        if ($request_processor->EditMetadataPosted()) {
            $this->getEditMetadataForm();
            return true;
        }
        
        if($request_processor->SaveMetadataPosted()){
            $this->processSaveMetadata();
            return true; 
        }

        if ($request_processor->redirectBackPosted()) {
            $this->getDefaultPage();
            return true;
        }

        throw new \Exception('error-request');
    }

    protected function getDefaultPage($error_message = '') {
        $user_is_a_sysop = $this->checkWhetherUserIsASysop();
        $this->viewer = new UserPageDefaultViewer();
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
    
    private function processSingleCollection(){
        $collection_title = $this->request_processor->getCollectionTitle();
        $this->setWrapperAndViewer('single_collection_posted');
        $single_collection_data = $this->wrapper->getSingleCollectionData($collection_title);
        return $this->wrapper->showSingleCollectionData($collection_title, $single_collection_data);
    }
    
    private function getEditMetadataForm($error_message = ''){
        $collection_title = $this->request_processor->getCollectionTitle();
        $link_back_to_manuscript_page = $this->request_processor->getLinkBackToManuscriptPage();
        $this->setWrapperAndViewer('edit_metadata_posted');
        $single_collection_data = $this->wrapper->getSingleCollectionMetadata($collection_title);
        return $this->wrapper->showEditCollectionMetadata($collection_title, $single_collection_data, $link_back_to_manuscript_page, $error_message);
    }

    private function temp() {

        if ($button_name === 'changetitle') {
            return $this->showEditTitle();
        }

        if ($button_name === 'submitedit') {
            return $this->processEditCollectionMetadata();
        }

        if ($button_name === 'submittitle') {
            return $this->processNewTitle();
        }
    }

    /**
     * This function processes the edit when submitting a new manuscript page title
     */
    private function processNewTitle() {

        global $wgWebsiteRoot, $wgNewManuscriptOptions;

        $web_root = $wgWebsiteRoot;
        $zoomimages_dirname = $wgNewManuscriptOptions['zoomimages_root_dir'];
        $original_images_dir = $wgNewManuscriptOptions['original_images_dir'];
        $manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];

        $user_name = $this->user_name;
        $manuscript_new_title = $this->manuscript_new_title;
        $manuscript_old_title = $this->manuscript_old_title;
        $selected_collection = $this->selected_collection;
        $manuscript_url_old_title = $this->manuscript_url_old_title;
        $max_length = $this->max_length;

        //if the new title and the old title are equal, do nothing and return 
        if ($manuscript_new_title === $manuscript_old_title) {
            $summary_page_wrapper = new summaryPageWrapper('submitedit', 0, 0, $user_name, "", "", $selected_collection);
            $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();
            return $this->showSingleCollection($single_collection_data);
        }

        //check for errors in $manuscript_new_title
        if (empty($manuscript_new_title)) {
            return $this->showEditTitle($this->msg('userpage-error-empty'));
        }
        elseif (strlen($manuscript_new_title) > $max_length) {
            return $this->showEditTitle($this->msg('userpage-error-editmax1') . " " . $max_length . " " . $this->msg('userpage-error-editmax2'));

            //allow only alphanumeric charachters 
        }
        elseif (!preg_match("/^[A-Za-z0-9]+$/", $manuscript_new_title)) {
            return $this->showEditTitle($this->msg('userpage-error-alphanumeric'));
        }

        $new_page_url = trim($manuscripts_namespace_url . $user_name . '/' . $manuscript_new_title);

        if (null !== Title::newFromText($new_page_url)) {

            $title_object = Title::newFromText($new_page_url);

            if ($title_object->exists()) {
                return $this->showEditTitle($this->msg('userpage-error-exists'));
            }
        }
        else {
            return $this->showEditTitle($this->msg('userpage-error-exists'));
        }

        $old_zoomimages = $web_root . DIRECTORY_SEPARATOR . $zoomimages_dirname . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_old_title;
        $new_zoomimages = $web_root . DIRECTORY_SEPARATOR . $zoomimages_dirname . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_new_title;

        $old_original_images = $web_root . DIRECTORY_SEPARATOR . $original_images_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_old_title;
        $new_original_images = $web_root . DIRECTORY_SEPARATOR . $original_images_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_new_title;

        //if the directories do not exist, do nothing and return 
        if (!is_dir($old_zoomimages) && !is_dir($old_original_images)) {
            $summary_page_wrapper = new summaryPageWrapper('submitedit', 0, 0, $user_name, "", "", $selected_collection);
            $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();
            return $this->showSingleCollection($single_collection_data);
        }

        //rename the zoomimages folder and the original images folder
        rename($old_zoomimages, $new_zoomimages);
        rename($old_original_images, $new_original_images);

        //get text from old wikipage
        $title_object = Title::newFromText($manuscript_url_old_title);
        $article_object = Wikipage::factory($title_object);
        $old_page_text = $article_object->getRawText();

        //create a new wikipage with the $old_page_text
        $title_object = Title::newFromText($new_page_url);
        $context = $this->getContext();
        $article = Article::newFromTitle($title_object, $context);
        $editor_object = new EditPage($article);
        $content_new = new wikitextcontent($old_page_text);

        $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97, false, null, $editor_object->contentFormat);

        if (!$doEditStatus->isOK()) {
            rename($new_zoomimages, $old_zoomimages);
            rename($new_original_images, $old_original_images);
            wfErrorLog($this->msg('userpage-error-wikipage') . $new_page_url . $this->msg('userpage-error3') . $manuscript_url_old_title . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            return $this->showEditTitle($this->msg('userpage-error-wikipage2'));
        }

        $dbw = wfGetDB(DB_MASTER);
        $dbw->begin(__METHOD__);

        //get the page id of the old page, and delete the old page
        $page_title = str_replace('Manuscripts:', '', $manuscript_url_old_title);
        $summary_page_wrapper = new summaryPageWrapper('submitedit', 0, 0, $user_name, "", "", $selected_collection, $page_title);
        $page_id = $summary_page_wrapper->retrievePageId();

        $dbw->delete(
            'page', //from
            array(
          'page_id' => $page_id
            ), //conditions
            __METHOD__
        );

        if (!$dbw->affectedRows() > 0) {
            $dbw->rollback(__METHOD__);
            rename($new_zoomimages, $old_zoomimages);
            rename($new_original_images, $old_original_images);
            wfErrorLog($this->msg('userpage-error-log1') . $new_page_url . $this->msg('userpage-error-log3') . $manuscript_url_old_title . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');

            return $this->showEditTitle($this->msg('userpage-error-delete'));
        }

        //update the 'manuscripts' table
        $dbw->update(
            'manuscripts', //select table
            array(//update values
          'manuscripts_title' => $manuscript_new_title,
          'manuscripts_url' => $new_page_url,
          'manuscripts_lowercase_title' => strtolower($manuscript_new_title),
            ), array(
          'manuscripts_url  = ' . $dbw->addQuotes($manuscript_url_old_title), //conditions
            ), //conditions
            __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            $dbw->rollback(__METHOD__);
            rename($new_zoomimages, $old_zoomimages);
            rename($new_original_images, $old_original_images);
            wfErrorLog($this->msg('userpage-error-log3') . $new_page_url . $this->msg('userpage-error-log3') . $manuscript_url_old_title . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');

            return $this->showEditTitle($this->msg('userpage-error-database'));
        }

        //redirect back if there were no errors          
        $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();
        return $this->showSingleCollection($single_collection_data);
    }

    /**     
     * Regular expression: 
     * /^[A-Za-z0-9\s]+$/
     * 
     * / = delimiter
     * ^ and $ = anchors. Start and end of line
     * /s = match spaces
     */
    private function processEditCollectionMetadata() {

        $max_length = $this->max_length;
        $textfield_array = $this->textfield_array;

        foreach ($textfield_array as $index => $textfield) {

            if (!empty($textfield)) {
                //wptextfield12 is the websource textfield, and wptextfield14 is the notes textfield
                if ($index !== 'wptextfield12' && $index !== 'wptextfield14') {

                    if (strlen($textfield) > $max_length) {
                        return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " " . $max_length . " " . $this->msg('userpage-error-editmax2'));

                        //allow alphanumeric charachters and whitespace  
                    }
                    elseif (!preg_match("/^[A-Za-z0-9\s]+$/", $textfield)) {
                        return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric'));
                    }
                }
                elseif ($index === 'wptextfield12') {

                    if (strlen($textfield) > $max_length) {
                        return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " " . $max_length . " " . $this->msg('userpage-error-editmax2'));

                        //allow alphanumeric charachters, whitespace, and '-./:'  
                    }
                    elseif (!preg_match("/^[A-Za-z0-9\-.\/:\s]+$/", $textfield)) {
                        return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric2'));
                    }
                }
                elseif ($index === 'wptextfield14') {

                    $length_textfield = strlen($textfield);
                    $max_charachters_notes = $max_length * 20;

                    if ($length_textfield > $max_charachters_notes) {
                        return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " " . $max_charachters_notes . " " . $this->msg('userpage-error-editmax3') . " " . $length_textfield . " " . $this->msg('userpage-error-editmax4'));

                        //allow alphanumeric charachters, whitespace, and ',.;!?' 
                    }
                    elseif (!preg_match("/^[A-Za-z0-9,.;!?\s]+$/", $textfield)) {
                        return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric3'));
                    }
                }
            }
        }

        $summary_page_wrapper = new summaryPageWrapper('submitedit', 0, 0, $this->user_name, "", "", $this->selected_collection);
        $status = $summary_page_wrapper->insertCollections($textfield_array);
        $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();

        if (isset($this->linkback)) {
            return $this->prepareRedirect();
        }

        return $this->showSingleCollection($single_collection_data);
    }

    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
    static function processInput($form_data) {
        return false;
    }

    protected function getViewer() {
        //empty because viewer has to be determined at runtime
        return null;
    }

    protected function getWrapper() {
        //empty because wrapper has to be determined at runtime   
        return null;
    }

    private function setWrapperAndViewer($button_name) {
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
            case 'single_collection_posted': 
            case 'edit_metadata_posted':    
                $this->wrapper = new AllCollectionsWrapper($this->user_name);
                $this->viewer = new UserPageCollectionsViewer($this->getOutput(), $this->user_name);
                break;
        }

        if (!isset($this->wrapper) || !isset($this->viewer)) {
            throw new \Exception('error-request');
        }
    }

    protected function getRequestProcessor() {
        return new UserPageRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator());
    }

}
