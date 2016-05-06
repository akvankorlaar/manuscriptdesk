<?php

/**
 * This file is part of the Manuscript Desk (github.com/akvankorlaar/manuscriptdesk)
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

    private $lowercase_alphabet;
    private $uppercase_alphabet;

    public function __construct($page_name) {
        parent::__construct($page_name);
    }

    protected function setVariables() {
        parent::setVariables();
        global $wgNewManuscriptOptions;

        //there is both a lowercase alphabet, and a uppercase alphabet, because the lowercase alphabet is used for the database query, and the uppercase alphabet
        //for the button values
        $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->lowercase_alphabet = array_merge(range('a', 'z'), $numbers);
        $this->uppercase_alphabet = array_merge(range('A', 'Z'), $numbers);
    }

    /**
     * Main entry point for Special Pages in the Manuscript Desk. Override to also allow access to users without an account
     */
    public function execute($subpage_arguments) {

        try {
            $this->setVariables();

            if ($this->request_processor->requestWasPosted()) {
                $this->processRequest();
                return true;
            }

            $this->getDefaultPage();
            return true;
        } catch (Exception $e) {
            $this->handleExceptions($e);
            return false;
        }
    }

    protected function processRequest() {

        $request_processor = $this->request_processor;

        if (!$request_processor->singleCollectionPosted()) {
            $this->processLetterOrButtonRequest();
            return true;
        }
        else {
            $this->processSingleCollectionDataRequest();
            return true;
        }
    }

    private function processLetterOrButtonRequest() {
        list($button_name, $offset) = $this->request_processor->getLetterOrButtonRequestValues($this->lowercase_alphabet);
        list($page_data, $next_offset) = $this->getLetterOrButtonDatabaseData($button_name, $offset);

        if (empty($page_data)) {
            return $this->getEmptyPageTitlesError($button_name);
        }

        return $this->getSingleLetterOrNumberPage($button_name, $page_data, $offset, $next_offset);
    }

    private function getLetterOrButtonDatabaseData($button_name, $offset) {
        $next_letter_alphabet = $this->getNextNumberOrLetterOfTheAlphabet($button_name);
        return $this->wrapper->getData($offset, $button_name, $next_letter_alphabet);
    }

    private function getSingleLetterOrNumberPage($button_name, $page_data, $offset, $next_offset) {

        $alphabet_numbers = $this->wrapper->getAlphabetNumbersWrapper()->getAlphabetNumbersData($this->getSpecialPageName());

        $this->viewer->showSingleLetterOrNumberPage(
            $alphabet_numbers, $this->uppercase_alphabet, $this->lowercase_alphabet, $button_name, $page_data, $offset, $next_offset
        );

        return true;
    }

    private function getEmptyPageTitlesError($button_name) {
        $alphabet_numbers = $this->wrapper->getAlphabetNumbersWrapper()->getAlphabetNumbersData($this->getSpecialPageName());
        $this->viewer->showEmptyPageTitlesError($alphabet_numbers, $this->uppercase_alphabet, $this->lowercase_alphabet, $button_name);
    }

    private function getNextNumberOrLetterOfTheAlphabet($button_name = '') {

        $lowercase_alphabet = $this->lowercase_alphabet;
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
        else {
            return 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
        }
    }

    private function processSingleCollectionDataRequest() {

        if (!$this instanceof SpecialAllCollections) {
            throw new \Exception('error-request');
        }

        $selected_collection = $this->request_processor->getCollectionTitle();
        $single_collection_data = $this->wrapper->getSingleCollectionData($selected_collection);
        $alphabet_numbers = $this->wrapper->getAlphabetNumbersWrapper()->getAlphabetNumbersData($this->getSpecialPageName());
        return $this->viewer->showSingleCollectionData($alphabet_numbers, $this->uppercase_alphabet, $this->lowercase_alphabet, $selected_collection, $single_collection_data, $alphabet_numbers);
    }

    protected function getDefaultPage($error_message = '') {
        $alphabet_numbers = $this->wrapper->getAlphabetNumbersWrapper()->getAlphabetNumbersData($this->getSpecialPageName());
        $this->viewer->showDefaultPage($error_message, $alphabet_numbers, $this->uppercase_alphabet, $this->lowercase_alphabet);
    }

    /**
     * Get the name of the special page
     * 
     * @return name of Special Page
     */
    abstract protected function getSpecialPageName();
}
