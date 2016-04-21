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

        $request_processor = $this->request_processor;
        $request_processor->checkEditToken($this->getUser());

        if ($request_processor->defaultPageWasPosted()) {
            $this->processDefaultPage();
            return true;
        }

        if ($request_processor->savePagePosted()) {
            $this->processSavePageRequest();
            return true;
        }

        if ($request_processor->redirectBackPosted()) {
            $this->getDefaultPage();
            return true;
        }

        throw new \Exception('collate-error-request');
    }

    protected function getDefaultPage($error_message = '') {
        $wrapper = $this->wrapper;
        $manuscripts_data = $wrapper->getManuscriptsData();
        $collection_data = $wrapper->getCollectionData();
        $this->viewer->showDefaultPage($error_message, $manuscripts_data, $collection_data);
        return true;
    }

    private function processDefaultPage() {
        list($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles) = $this->request_processor->getDefaultPageData();
        $page_titles = $this->getPageTitlesCorrespondingToPostedUrls($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles);
        $page_texts = $this->getTextsFromWikiPages($manuscript_urls, $collection_urls_data);
        $collatex_converter = $this->getCollatexConverter();
        $collatex_output = $collatex_converter->execute($page_texts);
        $imploded_page_titles = $this->createImplodedPageTitles($page_titles);
        $new_url = $this->makeUrlForNewPage($imploded_page_titles);
        $time = idate('U'); //time format (Unix Timestamp). This timestamp is used to see how old tempcollate values are
        $this->updateDatabase($page_titles, $imploded_page_titles, $new_url, $time, $collatex_output);
        $this->viewer->showCollatexOutput($page_titles, $collatex_output, $time);
        return true;
    }

    /**
     * This function processes the request when the user wants to save the collation table. Collate data is transferred from the 'tempcollate' table to
     * the 'collations' table, a new page is made, and the user is redirected to this page
     */
    private function processSavePageRequest() {
        $time_identifier = $this->request_processor->getSavePageData();
        $wrapper = $this->wrapper;
        list($new_url, $main_title, $main_title_lowercase, $page_titles, $collatex_output) = $wrapper->getSavedCollateAnalysisData($time_identifier);
        $wrapper->storeCollations($new_url, $main_title, $main_title_lowercase, $page_titles, $collatex_output);
        $wrapper->getAlphabetNumbersWrapper()->modifyAlphabetNumbersSingleValue($main_title_lowercase, 'AllCollations', 'add');
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
        $text_processor = $this->getTextProcessor();
        $page_texts_manuscripts = $this->getPageTextsForSingleManuscriptPages($text_processor, $manuscript_urls);
        $page_texts_collections = $this->getPageTextsForCollections($text_processor, $collection_data);
        return array_merge($page_texts_manuscripts, $page_texts_collections);
    }

    /**
     * This function intializes the $collate_wrapper, clears the tempcollate table, and inserts new data into the tempcollate table 
     */
    private function updateDatabase($titles_array, $main_title, $new_url, $time, $collatex_output) {
        $wrapper = $this->wrapper;
        $wrapper->clearOldCollatexOutput($time);
        $wrapper->storeTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output);
        return true;
    }

    private function getPageTextsForSingleManuscriptPages(ManuscriptDeskBaseTextProcessor $text_processor, array $manuscript_urls) {

        $texts = array();
        foreach ($manuscript_urls as $single_manuscript_url) {
            $single_page_text = $text_processor->getFilteredSinglePageText($single_manuscript_url);
            $texts[] = $single_page_text;
        }

        return $texts;
    }

    private function getPageTextsForCollections(ManuscriptDeskBaseTextProcessor $text_processor, array $collection_data) {

        $texts = array();
        foreach ($collection_data as $single_collection_urls) {
            $texts[] = $text_processor->getAllTextsForOneCollection($single_collection_urls);
        }

        return $texts;
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

    public function setViewer($object = null) {
        
        if(isset($this->viewer)){
            return;
        }
        
        return $this->viewer = isset($object) ? $object : new CollateViewer($this->getOutput());
    }

    public function setWrapper($object = null) {
        
        if(isset($this->wrapper)){
            return;
        }
        
        return $this->wrapper = isset($object) ? $object : new CollateWrapper(new AlphabetNumbersWrapper(), new SignatureWrapper(), $this->user_name);
    }

    public function setRequestProcessor($object = null) {
        
        if(isset($this->request_processor)){
            return;
        }
        
        return $this->request_processor = isset($object) ? $object : new CollateRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator());
    }

    protected function getCollatexConverter() {
        return new CollatexConverter();
    }
    
    protected function getTextProcessor(){
        return new ManuscriptDeskBaseTextProcessor();
    }

}
