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
abstract class ManuscriptDeskBaseWrapper {

    protected $user_name;

    public function __construct($user_name = null) {
        $this->user_name = $user_name;
    }

    /**
     * Assert whether $user_name has created the current $page_title_with_namespace
     */
    public function currentUserCreatedThePage($current_user_name, $partial_url) {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from 
            array(
          'manuscripts_user', //values
          'manuscripts_url',
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($partial_url),
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('manuscriptdeskbase-error-database');
        }

        $s = $res->fetchObject();

        if ($s->manuscripts_user !== $current_user_name) {
            return false;
        }

        return true;
    }

    /**
     * Subtract entries in the alphabetnumbers table when a page is deleted
     */
    public function modifyAlphabetNumbersSingleValue($main_title_lowercase, $alphabetnumbers_context, $mode) {

        $first_character_of_page = $this->getFirstCharachterOfTitle($main_title_lowercase);
        $number_of_pages_starting_with_this_charachter = $this->getAlphabetNumbersSingleValue($first_character_of_page, $alphabetnumbers_context);

        if ($mode === 'add') {
            $new_number_of_pages_starting_with_this_charachter = $number_of_pages_starting_with_this_charachter + 1;
        }
        else {
            $new_number_of_pages_starting_with_this_charachter = $new_number_of_pages_starting_with_this_charachter <= 0 ? 0 : $number_of_pages_starting_with_this_charachter - 1;
        }

        $this->updateAlphabetNumbersSingleValue($first_character_of_page, $new_number_of_pages_starting_with_this_charachter, $alphabetnumbers_context);
        return;
    }

    /**
     * Get the number of pages that start with $first_charachter_of_page from the 'alphabetnumbers' table
     */
    private function getAlphabetNumbersSingleValue($first_charachter_of_page = '', $alphabetnumbers_context = '') {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'alphabetnumbers', //from
            array(
          $first_charachter_of_page, //values
            ), array(
          'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context), //conditions
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('database-error');
        }

        $s = $res->fetchObject();
        return (int) $s->$first_charachter_of_page;
    }

    protected function getFirstCharachterOfTitle($main_title_lowercase = '') {
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

    private function updateAlphabetNumbersSingleValue($first_charachter_of_page, $number_of_pages, $alphabetnumbers_context) {
        $dbw = wfGetDB(DB_MASTER);
        
        $dbw->update(
            'alphabetnumbers', //select table
            array(
          $first_charachter_of_page => $number_of_pages, //insert values
            ), array(
          'alphabetnumbers_context = ' . $dbw->addQuotes($alphabetnumbers_context),
            ), __METHOD__
        );
        
        return;
    }

    public function getAlphabetNumbersData($alphabetnumbers_context = '') {

        $dbr = wfGetDB(DB_SLAVE);
        $number_array = array();

        $res = $dbr->select(
            'alphabetnumbers', array(
          'a',
          'b',
          'c',
          'd',
          'e',
          'f',
          'g',
          'h',
          'i',
          'j',
          'k',
          'l',
          'm',
          'n',
          'o',
          'p',
          'q',
          'r',
          's',
          't',
          'u',
          'v',
          'w',
          'x',
          'y',
          'z',
          'zero',
          'one',
          'two',
          'three',
          'four',
          'five',
          'six',
          'seven',
          'eight',
          'nine',
            ), array(
          'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context), //contitions
            ), __METHOD__
        );

        //there should only be one result
        if ($res->numRows() === 1) {
            $s = $res->fetchObject();

            $number_array = array(
              $s->a,
              $s->b,
              $s->c,
              $s->d,
              $s->e,
              $s->f,
              $s->g,
              $s->h,
              $s->i,
              $s->j,
              $s->k,
              $s->l,
              $s->m,
              $s->n,
              $s->o,
              $s->p,
              $s->q,
              $s->r,
              $s->s,
              $s->t,
              $s->u,
              $s->v,
              $s->w,
              $s->x,
              $s->y,
              $s->z,
              $s->zero,
              $s->one,
              $s->two,
              $s->three,
              $s->four,
              $s->five,
              $s->six,
              $s->seven,
              $s->eight,
              $s->nine,
            );
        }

        return $number_array;
    }

    public function getManuscriptsLowercaseTitle($partial_url) {      
        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($partial_url),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();
        return $s->manuscripts_lowercase_title;
    }

    public function determineAlphabetNumbersContextFromCollectionTitle($collection_title) {
        if (!isset($collection_title) || $collection_title === 'none') {
            return 'SingleManuscriptPages';
        }
        else {
            return 'AllCollections';
        }
    }

}
