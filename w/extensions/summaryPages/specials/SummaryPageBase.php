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

abstract class SummaryPageBase extends ManuscriptDeskBaseSpecials {

    public $lowercase_alphabet;
    public $uppercase_alphabet;
    protected $max_on_page; //maximum manuscripts shown on a page
    protected $selected_collection;
    
    protected $database_wrapper;
    protected $viewer; 
    protected $form_data_getter;
    
    public function __construct($page_name) {
        parent::__construct($page_name);
    }
    
    protected function setVariables() {
        
        global $wgNewManuscriptOptions;
        
        //there is both a lowercase alphabet, and a uppercase alphabet, because the lowercase alphabet is used for the database query, and the uppercase alphabet
        //for the button values
        $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->lowercase_alphabet = array_merge(range('a', 'z'), $numbers);
        $this->uppercase_alphabet = array_merge(range('A', 'Z'), $numbers);
        $this->max_on_page = $wgNewManuscriptOptions['max_on_page'];
        
       // $this->database_wrapp
        
    }

    private function processRequest() {

        if (!$this->singleCollectionDataWasRequested()) {
            $this->processLetterOrButtonRequest();
            return true; 
        }

        $this->processSingleCollectionDataRequest();
        return true; 
    }

    private function processLetterOrButtonRequest() {
        list($button_name, $offset) = $this->getFormData();
        list($page_titles, $next_offset) = $this->getLetterOrButtonDatabaseData($button_name, $offset);
        
        if(empty($page_titles)){
            $this->prepareShowEmptyPageTitlesError();
        }
        
        $this->prepareShowSingleLetterOrNumberPage($button_name, $page_titles, $offset, $next_offset);
    }
    
    private function prepareShowSingleLetterOrNumberPage($button_name, $page_titles, $offset, $next_offset){
        
        $alphabet_numbers = $database_wrapper->getAlphabetNumbersData($this->getSpecialPageName());
        $viewer = $this->getViewer();
              
        $viewer->showSingleLetterOrNumberPage(
                $alphabet_numbers,
                $this->uppercase_alphabet,
                $this->lowercase_alphabet,              
                $button_name, 
                $page_titles, 
                $offset, 
                $next_offset, 
                $this->max_on_page); 
            
        return true; 
    }
    
    private function prepareShowEmptyPageTitlesError(){
        $database_wrapper = $this->getDatabaseWrapper();
        $alphabet_numbers = $database_wrapper->getAlphabetNumbersData($this->getSpecialPageName());
        $viewer = $this->getViewer();
        $viewer->showEmptyPageTitlesError($alphabet_numbers, $this->uppercase_alphabet,$this->lowercase_alphabet);
    }
    
    private function getFormData(){
        $form_data_getter = $this->getFormDataGetter();
        list($button_name, $offset) = $form_data_getter->getLetterOrButtonRequestValues($this->lowercase_alphabet); 
        return array($button_name, $offset);
    }
    
    private function getLetterOrButtonDatabaseData($button_name, $offset){
        $next_letter_alphabet = $this->getNextNumberOrLetterOfTheAlphabet($button);
        $database_wrapper = $this->getWrapper();
        return $database_wrapper->getData($button_name, $offset, $next_letter_alphabet, $this->max_on_page);
    }
    
    private function getNextNumberOrLetterOfTheAlphabet($button_name = '', array $lowercase_alphabet) {

        $next_letter = null;
        $index = array_search($button_name, $lowercase_alphabet);

        if ($index !== false) {
            $next_letter = isset($lowercase_alphabet[$index + 1]) ? $lowercase_alphabet[$index + 1] : null;
        }

        if ($next_letter) {
            return $next_letter;
        }

        if ($next_letter === null) {
            return '99999999999999999999999999999999999999999999999999';
        }

        return 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
    }
    
    private function processSingleCollectionDataRequest() {
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'singlecollection';
    }

    private function singleCollectionDataWasRequested() {
        $request = $this->getRequest();
        if ($request->getText('singlecollection') !== '') {
            return true;
        }

        return false;
    }

    protected function getDefaultPage() {
        $database_wrapper = $this->getWrapper();
        $alphabet_numbers = $database_wrapper->getAlphabetNumbersData($this->getSpecialPageName());
        $viewer = $this->getViewer();
        $viewer->showDefaultPage($alphabet_numbers);
    }

    private function temp() {
        $database_wrapper = new summaryPageWrapper('singlecollection', 0, 0, "", "", "", $this->selected_collection);
        $single_collection_data = $database_wrapper->retrieveFromDatabase();
        return $this->showSingleCollectionData($single_collection_data);
    }

    /**
     * Return viewer object for the special page
     * 
     * @return ManuscriptDeskBaseViewer object
     */
    abstract protected function getViewer();

    /**
     * Return wrapper object for the special page
     * 
     * @return ManuscriptDeskBaseWrapper object
     */
    abstract protected function getWrapper();
    
    /**
     * Get the form data getter object for the page
     * 
     * @return name of Special Page
     */
    abstract protected function getFormDataGetter();
    
    /**
     * Get the name of the special page
     * 
     * @return name of Special Page
     */
    abstract protected function getSpecialPageName();

}
