<?php

/**
 * This file is part of the collate extension
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
class ManuscriptDeskBaseWrapper {

    protected $user_name;

    public function __construct() {
        
    }

    /**
     * Assert whether $user_name has created the current $page_title_with_namespace
     */
    public function currentUserCreatedThePage($page_title_with_namespace = '', $user_name = '') {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from 
            array(
          'manuscripts_user', //values
          'manuscripts_url',
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($page_title_with_namespace),
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('manuscriptdeskbase-error-database');
        }

        $s = $res->fetchObject();

        if ($s->manuscripts_user !== $user_name) {
            return false;
        }

        return true;
    }

    /**
     * Subtract entries in the alphabetnumbers table when a page is deleted
     */  //AllCollations ..context information
    public function subtractAlphabetnumbers($main_title_lowercase = '', $alphabetnumbers_context = '') {

        if (!is_string($main_title_lowercase) || !is_string($alphabetnumbers_context)) {
            return true;
        }

        $first_character_of_page = $this->getFirstCharachter($main_title_lowercase);
        $number_of_pages_starting_with_this_charachter = $this->getAlphabetNumbersData($first_character_of_page);
        $new_number_of_pages_starting_with_this_charachter = $number_of_pages_starting_with_this_charachter - 1;
        $this->updateAlphabetNumbers($new_number_of_pages_starting_with_this_charachter, $alphabetnumbers_context);
    }

    /**
     * Get the number of pages that start with $first_charachter_of_page from the 'alphabetnumbers' table
     */
    private function getAlphabetNumbersData($first_charachter_of_page = '') {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'alphabetnumbers', //from
            array(//values
          $first_charachter_of_page,
            ), array(
          'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context),
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('database-error');
        }

        $s = $res->fetchObject();
        $number_of_pages_starting_with_this_charachter = (int) $s->$first_charachter_of_page;

        if ($intvalue < 0) {
            $intvalue = 0;
        }

        return $number_of_pages_starting_with_this_charachter;
    }

    private function getFirstCharachter($main_title_lowercase = '') {
        $first_char = substr($main_title_lowercase, 0, 1);

        if (preg_match('/[0-9]/', $first_char)) {

            switch ($first_char) {
                case '0':
                    $first_char = 'zero';
                    break;
                case '1':
                    $first_char = 'one';
                    break;
                case '2':
                    $first_char = 'two';
                    break;
                case '3':
                    $first_char = 'three';
                    break;
                case '4':
                    $first_char = 'four';
                    break;
                case '5':
                    $first_char = 'five';
                    break;
                case '6':
                    $first_char = 'six';
                    break;
                case '7':
                    $first_char = 'seven';
                    break;
                case '8':
                    $first_char = 'eight';
                    break;
                case '9':
                    $first_char = 'nine';
                    break;
            }
        }

        return $first_char;
    }

    private function updateAlphabetNumbers($number_of_pages = 0, $aphabetnumbers_context = '') {
        $dbw = wfGetDB(DB_MASTER);

        $dbw->update(
            'alphabetnumbers', //select table
            array(//insert values
          $first_char => $number_of_pages,
            ), array(
          'alphabetnumbers_context = ' . $dbw->addQuotes($alphabetnumbers_context),
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new \Exception('database-error');
        }

        return true;
    }

}
