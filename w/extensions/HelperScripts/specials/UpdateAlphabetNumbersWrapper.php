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
class UpdateAlphabetNumbersWrapper extends ManuscriptDeskBaseWrapper {

    public function updateAlphabetNumbersCollections() {
        $res = $this->getAllCollectionsAlphabetNumbersData();
        $alphabet_numbers = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'manuscripts_lowercase_collection');
        $this->storeAlphabetNumbers('AllCollections', $alphabet_numbers);
        return;
    }

    public function updateAlphabetNumbersSingleManuscriptPages() {
        $res = $this->getSingleManuscriptsAlphabetNumbersData();
        $alphabet_numbers = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'manuscripts_lowercase_title');
        $this->storeAlphabetNumbers('SingleManuscriptPages', $alphabet_numbers);
        return;
    }

    public function updateAlphabetNumbersCollations() {
        $res = $this->getAllCollationsAlphabetNumbersData();
        $alphabet_numbers = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'collations_main_title_lowercase');
        $this->storeAlphabetNumbers('AllCollations', $alphabet_numbers);
        return;
    }

    /**
     * Get the first letter of all of the collections, and check how many there are for a-z and 0-9 
     */
    private function getAllCollectionsAlphabetNumbersData() {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_lowercase_collection', //values
            ), array(
          'manuscripts_collection != ' . $dbr->addQuotes('none')
            )
            , __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_collection',
            )
        );

        return $res;
    }

    private function getSingleManuscriptsAlphabetNumbersData() {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_lowercase_title', //values
            ), array(
          'manuscripts_collection = ' . $dbr->addQuotes('none')
            )
            , __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
            )
        );

        return $res;
    }

    private function getAllCollationsAlphabetNumbersData() {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_main_title_lowercase', //values
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => 'collations_main_title_lowercase',
            )
        );

        return $res;
    }

    private function loopThroughResultsAndFillAlphabetNumbersTable($res, $result_name) {

        $last_full_result_name = null;
        $current_loop_letter_or_number = null;
        $current_number_of_entities = null;

        $alphabet_numbers = $this->getEmptyAlphabetNumbersArray();

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                if ($s->$result_name === $last_full_result_name && $result_name === 'manuscripts_lowercase_collection') {
                    continue;
                }

                $last_full_result_name = $s->$result_name;

                $current_letter_or_number = $this->getFirstCharachterOfTitle($s->$result_name);
                if ($current_letter_or_number === $current_loop_letter_or_number) {
                    $current_number_of_entities += 1;
                }
                else {
                    //special case on first loop: $current_number_of_entities is not set
                    if (isset($current_number_of_entities)) {
                        $this->insertIntoAlphabetNumbers($alphabet_numbers, $current_loop_letter_or_number, $current_number_of_entities);
                        $current_number_of_entities = 0;
                    }

                    $current_loop_letter_or_number = $current_letter_or_number;
                    $current_number_of_entities += 1;
                }
            }
        }

        $this->insertIntoAlphabetNumbers($alphabet_numbers, $current_loop_letter_or_number, $current_number_of_entities);

        return $alphabet_numbers;
    }

    private function insertIntoAlphabetNumbers(array &$alphabet_numbers, $current_collection_letter_or_number, $current_number_of_collections) {

        if (!isset($alphabet_numbers[$current_collection_letter_or_number])) {
            throw new \Exception('error-database');
        }

        $alphabet_numbers[$current_collection_letter_or_number] = $current_number_of_collections;
    }

    private function storeAlphabetNumbers($alphabetnumbers_context, array $alphabet_numbers) {

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

    private function getEmptyAlphabetNumbersArray() {

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

}
