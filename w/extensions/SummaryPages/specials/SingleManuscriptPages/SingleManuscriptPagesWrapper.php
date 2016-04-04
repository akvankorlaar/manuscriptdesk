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
class SingleManuscriptPagesWrapper extends ManuscriptDeskBaseWrapper {

    public function getData($button_name, $offset = '', $next_letter_alphabet = '') {

        global $wgNewManuscriptOptions;
        $max_on_page = $wgNewManuscriptOptions['max_on_page'];

        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();
        $next_offset = null;

        if (isset($this->user_name)) {
            $conditions = array(
              'manuscripts_user = ' . $dbr->addQuotes($this->user_name),
              'manuscripts_collection = ' . $dbr->addQuotes('none'),);
        }
        else {
            $conditions = array(
              'manuscripts_lowercase_title >= ' . $dbr->addQuotes($button_name),
              'manuscripts_lowercase_title < ' . $dbr->addQuotes($next_letter_alphabet),
              'manuscripts_collection =' . $dbr->addQuotes('none'),
            );
        }

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
          'manuscripts_user',
          'manuscripts_url',
          'manuscripts_date',
          'manuscripts_lowercase_title',
            ), $conditions
            , __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
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
                      'manuscripts_title' => $s->manuscripts_title,
                      'manuscripts_user' => $s->manuscripts_user,
                      'manuscripts_url' => $s->manuscripts_url,
                      'manuscripts_date' => $s->manuscripts_date,
                    );
                }
                else {
                    //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
                    $next_offset = ($offset) + ($max_on_page);
                    break;
                }
            }
        }

        return array($title_array, $next_offset);
    }

}
