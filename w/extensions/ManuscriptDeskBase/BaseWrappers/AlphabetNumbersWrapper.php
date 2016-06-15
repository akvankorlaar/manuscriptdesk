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
class AlphabetNumbersWrapper {

    public function __construct() {
        
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
            $new_number_of_pages_starting_with_this_charachter = $number_of_pages_starting_with_this_charachter <= 0 ? 0 : $number_of_pages_starting_with_this_charachter - 1;
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

    public function getFirstCharachterOfTitle($main_title_lowercase = '') {
        $first_char = strtolower(substr($main_title_lowercase, 0, 1));

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

    public function determineAlphabetNumbersContextFromCollectionTitle($collection_title) {
        if (!isset($collection_title) || $collection_title === 'none') {
            return 'SingleManuscriptPages';
        }
        else {
            return 'AllCollections';
        }
    }

    public function insertIntoAlphabetNumbers(array &$alphabet_numbers, $current_collection_letter_or_number, $current_number_of_collections) {

        if (!isset($alphabet_numbers[$current_collection_letter_or_number])) {
            throw new \Exception('error-database');
        }

        $alphabet_numbers[$current_collection_letter_or_number] = $current_number_of_collections;
    }

    public function storeAlphabetNumbers($alphabetnumbers_context, array $alphabet_numbers) {

        $dbw = wfGetDB(DB_MASTER);

        $dbw->update('alphabetnumbers', //select table
            array(//insert values
          'a' => $alphabet_numbers['a'],
          'b' => $alphabet_numbers['b'],
          'c' => $alphabet_numbers['c'],
          'd' => $alphabet_numbers['d'],
          'e' => $alphabet_numbers['e'],
          'f' => $alphabet_numbers['f'],
          'g' => $alphabet_numbers['g'],
          'h' => $alphabet_numbers['h'],
          'i' => $alphabet_numbers['i'],
          'j' => $alphabet_numbers['j'],
          'k' => $alphabet_numbers['k'],
          'l' => $alphabet_numbers['l'],
          'm' => $alphabet_numbers['m'],
          'n' => $alphabet_numbers['n'],
          'o' => $alphabet_numbers['o'],
          'p' => $alphabet_numbers['p'],
          'q' => $alphabet_numbers['q'],
          'r' => $alphabet_numbers['r'],
          's' => $alphabet_numbers['s'],
          't' => $alphabet_numbers['t'],
          'u' => $alphabet_numbers['u'],
          'v' => $alphabet_numbers['v'],
          'w' => $alphabet_numbers['w'],
          'x' => $alphabet_numbers['x'],
          'y' => $alphabet_numbers['y'],
          'z' => $alphabet_numbers['z'],
          'zero' => $alphabet_numbers['zero'],
          'one' => $alphabet_numbers['one'],
          'two' => $alphabet_numbers['two'],
          'three' => $alphabet_numbers['three'],
          'four' => $alphabet_numbers['four'],
          'five' => $alphabet_numbers['five'],
          'six' => $alphabet_numbers['six'],
          'seven' => $alphabet_numbers['seven'],
          'eight' => $alphabet_numbers['eight'],
          'nine' => $alphabet_numbers['nine'],
            ), array(
          'alphabetnumbers_context = ' . $dbw->addQuotes($alphabetnumbers_context),
            ), __METHOD__, 'IGNORE');

        return;
    }

    public function getEmptyAlphabetNumbersArray() {

        $alphabet_numbers = array(
          'a' => 0,
          'b' => 0,
          'c' => 0,
          'd' => 0,
          'e' => 0,
          'f' => 0,
          'g' => 0,
          'h' => 0,
          'i' => 0,
          'j' => 0,
          'k' => 0,
          'l' => 0,
          'm' => 0,
          'n' => 0,
          'o' => 0,
          'p' => 0,
          'q' => 0,
          'r' => 0,
          's' => 0,
          't' => 0,
          'u' => 0,
          'v' => 0,
          'w' => 0,
          'x' => 0,
          'y' => 0,
          'z' => 0,
          'zero' => 0,
          'one' => 0,
          'two' => 0,
          'three' => 0,
          'four' => 0,
          'five' => 0,
          'six' => 0,
          'seven' => 0,
          'eight' => 0,
          'nine' => 0,
        );

        return $alphabet_numbers;
    }

    /**
     * Get the first letter of all of the collections, and check how many there are for a-z and 0-9 
     */
    public function getAllManuscriptCollectionPages() {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_lowercase_collection', //values
            ), array(
          'manuscripts_collection != ' . $dbr->addQuotes('none'),
          'manuscripts_collection != ' . $dbr->addQuotes('')
            )
            , __METHOD__, array(
          'ORDER BY' => array('manuscripts_lowercase_collection'),
            )
        );

        return $res;
    }

    public function getAllSingleManuscriptPages() {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_lowercase_title', //values
            ), array(
          'manuscripts_collection = ' . $dbr->addQuotes('none')
            )
            , __METHOD__, array(
          'ORDER BY' => array('manuscripts_lowercase_title'),
            )
        );

        return $res;
    }

    public function getAllCollationsPages() {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_main_title_lowercase', //values
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => array('collations_main_title_lowercase'),
            )
        );

        return $res;
    }

    public function getAllStylometricAnalysisPages() {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_main_title_lowercase', //values
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => array('stylometricanalysis_main_title_lowercase'),
            )
        );

        return $res;
    }

}
