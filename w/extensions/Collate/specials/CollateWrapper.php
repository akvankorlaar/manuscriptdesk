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
class CollateWrapper extends ManuscriptDeskBaseWrapper {

    public function __construct($user_name = "") {
        $this->user_name = $user_name;
    }

    public function getCollectionData() {

        global $wgCollationOptions; 
        
        $user_name = $this->user_name;
        $dbr = wfGetDB(DB_SLAVE);
        $collection_data = array();
        $maximum_manuscripts = $wgCollationOptions['wgmax_collation_pages'];

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
          'manuscripts_url',
          'manuscripts_collection',
            ), array(
          'manuscripts_user = ' . $dbr->addQuotes($user_name), //conditions
          'manuscripts_collection != ' . $dbr->addQuotes("none"),
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_collection',
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //check if the current collection has been added
                if (!isset($collection_data[$s->manuscripts_collection])) {
                    $collection_data[$s->manuscripts_collection] = array(
                      'manuscripts_url' => array($s->manuscripts_url),
                      'manuscripts_title' => array($s->manuscripts_title),
                    );

                }
                //if the collection already has been added, append the new manuscripts_url to the current array
                else {
                    end($collection_data);
                    $key = key($collection_data);
                    $collection_data[$key]['manuscripts_url'][] = $s->manuscripts_url;
                    $collection_data[$key]['manuscripts_title'][] = $s->manuscripts_title;
                }
            }
        }

        //remove collections that contain too many pages. maximum_manuscripts - 1 is used, because otherwise no other checkbox can be selected
        foreach ($collection_data as $collection_name => &$single_collection_data) {
            if (count($single_collection_data['manuscripts_url']) > ($maximum_manuscripts - 1)) {
                unset($collection_data[$collection_name]);
            }
        }

        return $collection_data;
    }

    /**
     * This function fetches the data showing which pages have been created by the current user by retrieving this data from the 'manuscripts' table
     */
    public function getManuscriptsData() {
        
        global $wgCollationOptions; 

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;
        $manuscript_urls = array();
        $manuscript_titles = array();
        $minimum_manuscripts = $wgCollationOptions['wgmin_collation_pages'];

        //Database query
        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
          'manuscripts_url',
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_user = ' . $dbr->addQuotes($user_name), //conditions: the user should be the current user
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //add titles to the title array and url array     
                $manuscript_titles[] = $s->manuscripts_title;
                $manuscript_urls[] = $s->manuscripts_url;
            }
        }
        
        if (count($manuscript_urls) < $minimum_manuscripts) {
                throw new \Exception('collate-error-fewmanuscripts');
        }

        return array(
          'manuscript_urls' => $manuscript_urls, 
          'manuscript_titles' => $manuscript_titles
            );
    }

    /**
     * This function gets the stored collate values from 'tempcollate'
     */
    public function getTempcollate($time_identifier) {

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;

        //Database query
        $res = $dbr->select(
            'tempcollate', //from
            array(
          'tempcollate_user', //values
          'tempcollate_titles_array',
          'tempcollate_new_url',
          'tempcollate_main_title',
          'tempcollate_main_title_lowercase',
          'tempcollate_time',
          'tempcollate_collatex'
            ), array(
          'tempcollate_user = ' . $dbr->addQuotes($user_name), //conditions
          'tempcollate_time = ' . $dbr->addQuotes($time_identifier),
            ), __METHOD__
        );

        if ($res->numRows() === 1) {
            $s = $res->fetchObject();

            $titles_array = $s->tempcollate_titles_array;
            $new_url = $s->tempcollate_new_url;
            $main_title = $s->tempcollate_main_title;
            $main_title_lowercase = $s->tempcollate_main_title_lowercase;
            $collatex_output = $s->tempcollate_collatex;

            return array($titles_array, $new_url, $main_title, $main_title_lowercase, $collatex_output);
        }
        else {

            return false;
        }
    }

    /**
     * Insert the result of the collation into 'tempcollate', which will be used when the user wants to save the current table
     *  
     * @param type $collatex_output
     */
    public function storeTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output) {

        $titles_array = json_encode($titles_array);
        $main_title_lowercase = strtolower($main_title);

        $dbw = wfGetDB(DB_MASTER);

        $insert_values = $dbw->insert(
            'tempcollate', //select table
            array(//insert values
          'tempcollate_user' => $this->user_name,
          'tempcollate_titles_array' => $titles_array,
          'tempcollate_new_url' => $new_url,
          'tempcollate_main_title' => $main_title,
          'tempcollate_main_title_lowercase' => $main_title_lowercase,
          'tempcollate_time' => $time,
          'tempcollate_collatex' => $collatex_output
            ), __METHOD__, 'IGNORE'
        );

        if ($dbw->affectedRows()) {
            //insert succeeded
            return true;
        }
        else {
            //return error    
            return false;
        }
    }

    /**
     * This function checks if there are other stored values for this user in 'tempcollate'. If the time difference between $current_time
     * and $time of the stored values is larger than $this->hours_before_delete, the values will be deleted 
     */
    public function clearTempcollate($current_time) {

        global $wgCollationOptions;

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;
        $time_array = array();
        $hours_before_delete = $wgCollationOptions['tempcollate_hours_before_delete'];

        //Database query
        $res = $dbr->select(
            'tempcollate', //from
            array(
          'tempcollate_user', //values
          'tempcollate_time'
            ), array(
          'tempcollate_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__, array(
          'ORDER BY' => 'tempcollate_time',
            )
        );

        //while there are still titles in this query
        while ($s = $res->fetchObject()) {

            $time_array[] = $s->tempcollate_time;
        }

        foreach ($time_array as $index => $time) {

            if ($current_time - $time > ($hours_before_delete * 3600)) {
                $status = $this->deleteTempcollate($time);

                //deletion of an element failed, so something went wrong
                if (!$status) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * This function deletes entries from the 'tempcollate' table
     * 
     * @param type $time
     * @return boolean
     */
    private function deleteTempcollate($time) {

        $dbw = wfGetDB(DB_MASTER);
        $user_name = $this->user_name;

        $dbw->delete(
            'tempcollate', //from
            array(
          'tempcollate_user = ' . $dbw->addQuotes($user_name), //conditions
          'tempcollate_time = ' . $dbw->addQuotes($time),
            ), __METHOD__);

        if ($dbw->affectedRows()) {
            //something was deleted from the tempcollate table  
            return true;
        }
        else {
            //nothing was deleted
            return false;
        }
    }

    /**
     * This function stores the collation data in 'collations' when the user chooses to save the current table
     * 
     * @param type $new_url
     * @param type $main_title
     * @param type $main_title_lowercase
     * @return boolean
     */
    public function storeCollations($new_url, $main_title, $main_title_lowercase, $titles_array, $collatex_output) {

        $user_name = $this->user_name;
        $date = date("d-m-Y H:i:s");
        $main_title_lowercase = strtolower($main_title);

        $dbw = wfGetDB(DB_MASTER);
        $dbw->insert('collations', //select table
            array(//insert values
          'collations_user' => $user_name,
          'collations_url' => $new_url,
          'collations_date' => $date,
          'collations_main_title' => $main_title,
          'collations_main_title_lowercase' => $main_title_lowercase,
          'collations_titles_array' => $titles_array,
          'collations_collatex' => $collatex_output
            ), __METHOD__, 'IGNORE');
        if ($dbw->affectedRows()) {
            //insert succeeded
            return true;
        }
        else {
            //return error
            return false;
        }
    }

    /**
     * This function increments the alphabetnumbers table. The first letter or digit of the $posted_title is extracted, and the value is incremented in the appropriate place.
     * The alphabetnumbers table is used to visualize the number of pages in different categories (used in for example: Special:AllCollections)
     * 
     * @param type $collection_title
     * @param type $user_name    
     * @return boolean
     */
    public function storeAlphabetnumbers($main_title_lowercase) {

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

        $alphabetnumbers_context = 'AllCollations';

        //first select the old value, increment it by one, and update the value. Ideally this should be done in 1 update statement, but there seems to be no other way using
        //Mediawiki's database wrapper
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'alphabetnumbers', //from
            array(//values
          $first_char,
            ), array(
          'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context),
            ), __METHOD__
        );

        //there should only be 1 result
        if ($res->numRows() === 1) {
            $s = $res->fetchObject();
            $intvalue = (int) (($s->$first_char) + 1);

            $dbw = wfGetDB(DB_MASTER);

            $dbw->update(
                'alphabetnumbers', //select table
                array(//insert values
              $first_char => $intvalue,
                ), array(
              'alphabetnumbers_context = ' . $dbw->addQuotes($alphabetnumbers_context),
                ), __METHOD__
            );

            if ($dbw->affectedRows()) {
                return true;
            }
            else {
                return false;
            }
        }

        return false;
    }

    /**
     * This function retrieves data from the 'collations' table
     */
    public function getCollationsData($page_title_with_namespace = '') {

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_user', //values
          'collations_url',
          'collations_date',
          'collations_titles_array',
          'collations_collatex'
            ), array(
          'collations_url = ' . $dbr->addQuotes($page_title_with_namespace), //conditions  
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();

        return array(
          'user_name' => $s->collations_user,
          'date' => $s->collations_date,
          'titles_array' => $s->collations_titles_array,
          'collatex_output' => $s->collations_collatex,
        );
    }

    /**
     * This function deletes the entry for corresponding to the page in the 'collations' table
     */
    public function deleteDatabaseEntry($page_title_with_namespace = '') {

        $dbw = wfGetDB(DB_MASTER);

        $dbw->delete(
            'collations', //from
            array(
          'collations_url' => $page_title_with_namespace //conditions
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new \Exception('database-error');
        }

        return true;
    }

    public function getManuscriptsLowercaseTitle($page_title_with_namespace = '') {
        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;

        //Database query
        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_url', //values
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($page_title_with_namespace),
            ), __METHOD__
        );

        if ($res->numRows() !== 0) {
            throw new \Exception('collate-error-database');
        }

        return $res->fetchObject()->main_lowercase_title;
    }

}
