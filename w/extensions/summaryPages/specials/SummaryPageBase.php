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
class SummaryPageBase extends ManuscriptDeskBaseSpecials {

    public $lowercase_alphabet;
    public $uppercase_alphabet;
    protected $page_name;
    protected $max_on_page; //maximum manuscripts shown on a page
    protected $next_page_possible = false;
    protected $previous_page_possible = false;
    protected $is_number = false;
    protected $offset = 0;
    protected $next_offset;
    protected $selected_collection;

    public function __construct($page_name) {
        $this->page_name = $page_name;
        parent::__construct($page_name);
    }

    protected function setVariables() {
        //there is both a lowercase alphabet, and a uppercase alphabet, because the lowercase alphabet is used for the database query, and the uppercase alphabet
        //for the button values
        $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $this->lowercase_alphabet = array_merge(range('a', 'z'), $numbers);
        $this->uppercase_alphabet = array_merge(range('A', 'Z'), $numbers);
    }

    /**
     * Main entry point for the page
     */
    public function execute() {

        try {
            $this->setVariables();

            if ($this->requestWasPosted()) {
                $this->processRequest();
                return true;
            }

            $this->getDefaultPage();
            return true;
        } catch (Exception $e) {
            $this->handleExceptions($e);
            return true;
        }
    }

    private function processRequest() {

        if ($this->singleCollectionDataWasRequested()) {
            return $this->processSingleCollectionData();
        }

        return $this->processLetterOrButtonRequest();
    }

    private function processSingleCollectionData() {
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'singlecollection';
    }

    protected function processLetterOrButtonRequest() {
        $data = $this->getLetterOrButtonRequestValues();
        $next_letter_alphabet = $this->getNextNumberOrLetterOfTheAlphabet($button);
        $database_wrapper = $this->getDatabaseWrapper();
        $data = $database_wrapper->retrieveFromDatabase();
        $this->showPage($data);
    }

    private function getLetterOrButtonRequestValues() {
        global $wgNewManuscriptOptions;

        $max_on_page = $wgNewManuscriptOptions['max_on_page'];
        $lowercase_alphabet = $this->lowercase_alphabet;

        foreach ($request->getValueNames()as $value) {

            if (in_array($value, $lowercase_alphabet)) {
                $button_name = strval($value);
            }
            elseif ($value === 'offset') {
                $offset = (int) $request->getText($value);

                if (!$offset >= 0) {
                    throw new \Exception('error-request');
                }

                if ($offset >= $max_on_page) {
                    $previous_page_possible = true;
                }

                $offset = $offset;
            }
        }

        if (!isset($button_name)) {
            throw new \Exception('error-request');
        }


        if (is_numeric($button_name)) {
            $is_number = true;
        }

        return array($button_name, $offset, $previous_page_possible, $is_number);
    }

    private function singleCollectionDataWasRequested() {
        $request = $this->getRequest();
        if ($request->getText('singlecollection') !== '') {
            return true;
        }

        return false;
    }

    private function getDefaultPage() {
        $database_wrapper = $this->getWrapper();
        $database_wrapper->getAlphabetNumbersData($this->page_name);
        $viewer = $this->getViewer();
        $viewer->showDefaultPage($data);
    }

    private function temp() {
        $database_wrapper = new summaryPageWrapper('singlecollection', 0, 0, "", "", "", $this->selected_collection);
        $single_collection_data = $database_wrapper->retrieveFromDatabase();
        return $this->showSingleCollectionData($single_collection_data);
    }

    protected function getNextNumberOrLetterOfTheAlphabet($button_name = '', array $lowercase_alphabet) {

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

}
