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
class AllStylometricAnalysisWrapper implements SummaryPageWrapperInterface {

    private $alphabetnumbers_wrapper;
    private $signature_wrapper;
    private $user_name;

    public function __construct(AlphabetNumbersWrapper $alphabetnumbers_wrapper, SignatureWrapper $signature_wrapper = null) {
        $this->alphabetnumbers_wrapper = $alphabetnumbers_wrapper;
        $this->signature_wrapper = $signature_wrapper;
    }

    public function setUserName($user_name) {

        if (isset($this->user_name)) {
            return;
        }

        return $this->user_name = $user_name;
    }

    public function getData($offset, $button_name = '', $next_letter_alphabet = '') {

        global $wgNewManuscriptOptions;
        $max_on_page = $wgNewManuscriptOptions['max_on_page'];

        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();
        $next_offset = null;

        if (isset($this->user_name)) {
            $conditions = array('stylometricanalysis_user = ' . $dbr->addQuotes($this->user_name));
        }
        else {
            $conditions = array(
              'stylometricanalysis_main_title_lowercase >= ' . $dbr->addQuotes($button_name),
              'stylometricanalysis_main_title_lowercase < ' . $dbr->addQuotes($next_letter_alphabet)
            );
        }

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_user', //values
          'stylometricanalysis_new_page_url',
          'stylometricanalysis_date',
          'stylometricanalysis_main_title',
          'stylometricanalysis_signature',
          'stylometricanalysis_main_title_lowercase'
            ), $conditions
            , __METHOD__, array(
          'ORDER BY' => array('CAST(stylometricanalysis_main_title_lowercase AS UNSIGNED)', 'stylometricanalysis_main_title_lowercase'),
          'LIMIT' => $max_on_page + 1,
          'OFFSET' => $offset,
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //add titles to the title array as long as it is not bigger than max_on_page
                if (count($title_array) < $max_on_page) {

                    $title_array[] = array(
                      'stylometricanalysis_user' => $s->stylometricanalysis_user,
                      'stylometricanalysis_new_page_url' => $s->stylometricanalysis_new_page_url,
                      'stylometricanalysis_date' => $s->stylometricanalysis_date,
                      'stylometricanalysis_main_title' => $s->stylometricanalysis_main_title,
                      'stylometricanalysis_signature' => $s->stylometricanalysis_signature,
                    );

                    //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
                }
                else {
                    $next_offset = ($offset) + ($max_on_page);
                    break;
                }
            }
        }

        return array($title_array, $next_offset);
    }

    public function getAlphabetNumbersWrapper() {
        return $this->alphabetnumbers_wrapper;
    }

    public function getSignatureWrapper() {
        return $this->signature_wrapper;
    }

}
