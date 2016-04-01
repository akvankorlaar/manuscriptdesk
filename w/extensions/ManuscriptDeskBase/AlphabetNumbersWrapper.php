<?php

/**
 * This file is part of the NewManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */
class AlphabetNumbersWrapper extends ManuscriptDeskBaseWrapper {

    public function updateAlphabetNumbersCollections() {
        $empty_alphabet_numbers = $this->getEmptyAlphabetNumbersArray();
        $filled_alphabet_numbers = $this->fillAlphabetNumbers($empty_alphabet_numbers);
        //update alphabetnumbers table
    }

    private function fillAlphabetNumbers(array $alphabet_numbers) {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_collection', //values
            ), array(
          'manuscripts_collection != ' . $dbr->addQuotes('none')
            )
            , __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_collection',
            )
        );

        $current_collection_letter_or_number = null;
        $current_number_of_collections = null;

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {
                $current_letter_or_number = $this->getFirstCharachter($s->manuscripts_collection);
                if ($current_letter_or_number === $current_collection_letter_or_number) {
                    $current_number_of_collections += 1;
                }
                else {
                    $current_collection_letter_or_number = $current_letter_or_number;

                    //special case when $current_collection_letter_or_number === null
                    if (!isset($current_number_of_collections)) {
                        continue;
                    }

                    $this->insertIntoAlphabetNumbers($alphabet_numbers, $current_collection_letter_or_number, $current_number_of_collections);
                    $current_number_of_collections = 0;
                }
            }

            $this->insertIntoAlphabetNumbers($alphabet_numbers, $current_collection_letter_or_number, $current_number_of_collections);
        }

        return $alphabet_numbers;
    }

    private function insertIntoAlphabetNumbers(array &$alphabet_numbers, $current_collection_letter_or_number, $current_number_of_collections) {

        if (!isset($alphabet_numbers[$current_collection_letter_or_number])) {
            throw new \Exception('error-database');
        }

        $alphabet_numbers[$current_collection_letter_or_number] = $current_number_of_collections;
    }

    private function getFirstCharachter($main_title_lowercase) {
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
