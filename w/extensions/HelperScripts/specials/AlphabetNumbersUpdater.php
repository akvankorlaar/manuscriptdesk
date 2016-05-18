<?php

/**
 * This file is part of the ManuscriptDesk (github.com/akvankorlaar/manuscriptdesk)
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
class AlphabetNumbersUpdater {

    private $alphabetnumbers_wrapper;

    public function __construct(AlphabetNumbersWrapper $alphabetnumbers_wrapper) {
        $this->alphabetnumbers_wrapper = $alphabetnumbers_wrapper;
    }

    public function execute() {
        $this->updateAlphabetNumbersCollections();
        $this->updateAlphabetNumbersSingleManuscriptPages();
        $this->updateAlphabetNumbersCollations();
        $this->updateAlphabetNumbersStylometricAnalysis();
    }

    public function updateAlphabetNumbersCollections() {
        $alphabetnmbers_wrapper = $this->alphabetnumbers_wrapper;
        $res = $alphabetnmbers_wrapper->getAllManuscriptCollectionPages();
        $filled_alphabetnumbers_array = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'manuscripts_lowercase_collection');
        return $alphabetnmbers_wrapper->storeAlphabetNumbers('AllCollections', $filled_alphabetnumbers_array);
    }

    public function updateAlphabetNumbersSingleManuscriptPages() {
        $alphabetnumbers_wrapper = $this->alphabetnumbers_wrapper;
        $res = $alphabetnumbers_wrapper->getAllSingleManuscriptPages();
        $filled_alphabetnumbers_array = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'manuscripts_lowercase_title');
        return $alphabetnumbers_wrapper->storeAlphabetNumbers('SingleManuscriptPages', $filled_alphabetnumbers_array);
    }

    public function updateAlphabetNumbersCollations() {
        $alphabetnumbers_wrapper = $this->alphabetnumbers_wrapper;
        $res = $alphabetnumbers_wrapper->getAllCollationsPages();
        $filled_alphabetnumbers_array = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'collations_main_title_lowercase');
        return $alphabetnumbers_wrapper->storeAlphabetNumbers('AllCollations', $filled_alphabetnumbers_array);
    }

    public function updateAlphabetNumbersStylometricAnalysis() {
        $alphabetnumbers_wrapper = $this->alphabetnumbers_wrapper;
        $res = $alphabetnumbers_wrapper->getAllStylometricAnalysisPages();
        $filled_alphabetnumbers_array = $this->loopThroughResultsAndFillAlphabetNumbersTable($res, 'stylometricanalysis_main_title_lowercase');
        return $alphabetnumbers_wrapper->storeAlphabetNumbers('AllStylometricAnalysis', $filled_alphabetnumbers_array);
    }

    private function loopThroughResultsAndFillAlphabetNumbersTable($res, $result_name) {
        $alphabetnumbers_wrapper = $this->alphabetnumbers_wrapper;

        $last_full_result_name = null;
        $current_loop_letter_or_number = null;
        $current_number_of_entities = null;

        $alphabetnumbers_array = $alphabetnumbers_wrapper->getEmptyAlphabetNumbersArray();

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //only add the first occurrence of the name in case of collections
                if (strtolower($s->$result_name) === strtolower($last_full_result_name) && $result_name === 'manuscripts_lowercase_collection') {
                    continue;
                }

                $last_full_result_name = $s->$result_name;

                $current_letter_or_number = $alphabetnumbers_wrapper->getFirstCharachterOfTitle($s->$result_name);
                //as long as the letter or number stays the same, increment the value
                if ($current_letter_or_number === $current_loop_letter_or_number) {
                    $current_number_of_entities += 1;
                }
                else {
                    //when the letter or number is not the same, insert the current value, and reset it. isset($current_number_of_entities) is only needed for
                    //a check during the first loop, when $current_loop_letter_or_number is still null
                    if (isset($current_number_of_entities)) {
                        $alphabetnumbers_wrapper->insertIntoAlphabetNumbers($alphabetnumbers_array, $current_loop_letter_or_number, $current_number_of_entities);
                        $current_number_of_entities = 0;
                    }

                    //set the new letter or number, increment the value, and go to the next loop
                    $current_loop_letter_or_number = $current_letter_or_number;
                    $current_number_of_entities += 1;
                }
            }

            //also insert the last letter or number and its corresponding value
            $alphabetnumbers_wrapper->insertIntoAlphabetNumbers($alphabetnumbers_array, $current_loop_letter_or_number, $current_number_of_entities);
        }

        return $alphabetnumbers_array;
    }

}
