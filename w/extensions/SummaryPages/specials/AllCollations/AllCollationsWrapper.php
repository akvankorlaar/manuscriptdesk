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
class AllCollationsWrapper extends ManuscriptDeskBaseWrapper {

    public function getData($offset, $button_name = '', $next_letter_alphabet = '') {

        global $wgNewManuscriptOptions;
        $max_on_page = $wgNewManuscriptOptions['max_on_page'];

        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();
        $next_offset = null;

        if (isset($this->user_name)) {
            $conditions = array('collations_user = ' . $dbr->addQuotes($this->user_name));
        }
        else {
            $conditions = array(
              'collations_main_title_lowercase >= ' . $dbr->addQuotes($button_name),
              'collations_main_title_lowercase < ' . $dbr->addQuotes($next_letter_alphabet)
            );
        }

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_user', //values
          'collations_url',
          'collations_date',
          'collations_main_title',
          'collations_main_title_lowercase'
            ), $conditions
            , __METHOD__, array(
          'ORDER BY' => 'collations_main_title_lowercase',
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
                      'collations_user' => $s->collations_user,
                      'collations_url' => $s->collations_url,
                      'collations_date' => $s->collations_date,
                      'collations_main_title' => $s->collations_main_title,
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

}
