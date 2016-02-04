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
 * 
 */

class StylometricAnalysisWrapper {

    private $user_name;

    public function __construct($user_name = '') {
        $this->user_name = $user_name;
    }

    /**
     * This function checks if any uploaded manuscripts are part of a larger collection of manuscripts by retrieving data from the 'manuscripts' table
     */
    public function checkForManuscriptCollections($minimum_pages_per_collection = 0, $minimum_collections = 0) {

        $dbr = wfGetDB(DB_SLAVE);
        $collection_urls = array();
        $user_name = $this->user_name;

        //Database query
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
                if (!isset($collection_urls[$s->manuscripts_collection])) {
                    $collection_urls[$s->manuscripts_collection] = array(
                      'manuscripts_url' => array($s->manuscripts_url),
                      'manuscripts_title' => array($s->manuscripts_title),
                    );
                }
                //if the collection already has been added, append the new manuscripts_url to the current array
                else {
                    end($collection_urls);
                    $key = key($collection_urls);
                    $collection_urls[$key]['manuscripts_url'][] = $s->manuscripts_url;
                    $collection_urls[$key]['manuscripts_title'][] = $s->manuscripts_title;
                }
            }
        }

        //remove collections with less pages than $this->minimum_pages_per_collection from the list
        foreach ($collection_urls as $collection_name => &$small_url_array) {
            if (count($small_url_array['manuscripts_url']) < $minimum_pages_per_collection) {
                unset($collection_urls[$collection_name]);
            }
        }

        if (count($collection_urls) < $minimum_collections) {
            throw new Exception('stylometricanalysis-error-fewcollections');
        }

        return $collection_urls;
    }

    /**
     * This function checks if values should be removed from the 'tempstylometricanalysis' table
     */
    public function clearOldPystylOutput($time = 0) {

        global $wgStylometricAnalysisOptions;

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;
        $time_array = array();
        $full_outputpath1_array = array();
        $full_outputpath2_array = array();
        $hours_before_delete = $wgStylometricAnalysisOptions['tempstylometricanalysis_hours_before_delete'];

        //Database query
        $res = $dbr->select(
            'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time', //values
          'tempstylometricanalysis_user',
          'tempstylometricanalysis_full_outputpath1',
          'tempstylometricanalysis_full_outputpath2',
            ), array(
          'tempstylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__, array(
          'ORDER BY' => 'tempstylometricanalysis_time',
            )
        );

        //while there are still titles in this query
        while ($s = $res->fetchObject()) {

            $time_array[] = $s->tempstylometricanalysis_time;
            $full_outputpath1_array[] = $s->tempstylometricanalysis_full_outputpath1;
            $full_outputpath2_array[] = $s->tempstylometricanalysis_full_outputpath2;
        }

        foreach ($time_array as $index => $old_time) {

            if ($time - $old_time > ($hours_before_delete * 3600)) {

                $old_full_outputpath1 = $full_outputpath1_array[$index];
                $old_full_outputpath2 = $full_outputpath2_array[$index];

                $this->deleteOldPystylOutputImages($old_full_outputpath1, $old_full_outputpath2);
                $this->deleteOldEntriesFromTempstylometricanalysisTable($old_time);
            }
        }

        return true;
    }

    private function deleteOldPystylOutputImages($old_full_outputpath1, $old_full_outputpath2) {

        if (!is_file($old_full_outputpath1) || !is_file($old_full_outputpath2)) {
            throw new Exception('stylometricanalysis-error-database');
        }

        unlink($old_full_outputpath1);
        unlink($old_full_outputpath2);

        return true;
    }

    private function deleteOldEntriesFromTempstylometricanalysisTable($old_time) {

        $dbw = wfGetDB(DB_MASTER);
        $user_name = $this->user_name;

        $dbw->delete(
            'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time = ' . $dbw->addQuotes($old_time),
          'tempstylometricanalysis_user = ' . $dbw->addQuotes($user_name), //conditions
            ), __METHOD__);

        if (!$dbw->affectedRows()) {
            throw new Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function storeTempStylometricAnalysis($time = 0, $full_outputpath1, $full_outputpath2, $full_linkpath1, $full_linkpath2, array $config_array, $new_page_url, $date) {

        $dbw = wfGetDB(DB_MASTER);

        $user_name = $this->user_name;
        $json_config_array = json_encode($config_array);

        $dbw->insert(
            'tempstylometricanalysis', //select table
            array(//insert values
          'tempstylometricanalysis_time' => $time,
          'tempstylometricanalysis_user' => $user_name,
          'tempstylometricanalysis_full_outputpath1' => $full_outputpath1,
          'tempstylometricanalysis_full_outputpath2' => $full_outputpath2,
          'tempstylometricanalysis_full_linkpath1' => $full_linkpath1,
          'tempstylometricanalysis_full_linkpath2' => $full_linkpath2,     
          'tempstylometricanalysis_json_config_array' => $json_config_array,
          'tempstylometricanalysis_new_page_url' => $new_page_url,        
          'tempstylometricanalysis_date' => $date,
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function transferDataFromTempStylometricAnalysisToStylometricAnalysisTable($time = 0) {

        $dbr = wfGetDB(DB_SLAVE);

        $user_name = $this->user_name;

        //Database query
        $res = $dbr->select(
            'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time',
          'tempstylometricanalysis_user',
          'tempstylometricanalysis_full_outputpath1',
          'tempstylometricanalysis_full_outputpath2',
          'tempstylometricanalysis_full_linkpath1',
          'tempstylometricanalysis_full_linkpath2',    
          'tempstylometricanalysis_json_config_array',
          'tempstylometricanalysis_new_page_url',
          'tempstylometricanalysis_date'
            ), array(
          'tempstylometricanalysis_time =' . $dbr->addQuotes($time),
          'tempstylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new Exception('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();

        $full_outputpath1 = $s->tempstylometricanalysis_full_outputpath1;
        $full_outputpath2 = $s->tempstylometricanalysis_full_outputpath2;
        $full_linkpath1 = $s->tempstylometricanalysis_full_linkpath1;
        $full_linkpath2 = $s->tempstylometricanalysis_full_linkpath2; 
        $json_config_array = $s->tempstylometricanalysis_json_config_array;
        $new_page_url = $s->tempstylometricanalysis_new_page_url;
        $date = $s->tempstylometricanalysis_date;

        $dbw = wfGetDB(DB_MASTER);

        $dbw->insert(
            'stylometricanalysis', //select table
            array(//insert values
          'stylometricanalysis_time' => $time,
          'stylometricanalysis_user' => $user_name,
          'stylometricanalysis_full_outputpath1' => $full_outputpath1,
          'stylometricanalysis_full_outputpath2' => $full_outputpath2,
          'stylometricanalysis_full_linkpath1' => $full_linkpath1,
          'stylometricanalysis_full_linkpath2' => $full_linkpath2,     
          'stylometricanalysis_json_config_array' => $json_config_array,
          'stylometricanalysis_new_page_url' => $new_page_url,
          'stylometricanalysis_date' => $date,
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function getNewPageUrl($time = 0) {

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;

        //Database query
        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_time',
          'stylometricanalysis_user',
          'stylometricanalysis_new_page_url',
            ), array(
          'stylometricanalysis_time =' . $dbr->addQuotes($time),
          'stylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new Exception('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();

        return $s->stylometricanalysis_new_page_url;
    }

    public function getStylometricanalysisData($url_with_namespace) {

        $dbr = wfGetDB(DB_SLAVE);
        $data = array();
        $user_name = $this->user_name;

        //Database query
        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_time',
          'stylometricanalysis_user',
          'stylometricanalysis_full_outputpath1',
          'stylometricanalysis_full_outputpath2',
          'stylometricanalysis_full_linkpath1',
          'stylometricanalysis_full_linkpath2',    
          'stylometricanalysis_json_config_array',
          'stylometricanalysis_new_page_url',
          'stylometricanalysis_date',
            ), array(
          'stylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
          'stylometricanalysis_new_page_url = ' . $dbr->addQuotes($url_with_namespace),
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new Exception('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();

        $data['time'] = $s->stylometricanalysis_time;
        $data['user'] = $s->stylometricanalysis_user;
        $data['full_outputpath1'] = $s->stylometricanalysis_full_outputpath1;
        $data['full_outputpath2'] = $s->stylometricanalysis_full_outputpath2;
        $data['full_linkpath1'] = $s->stylometricanalysis_full_linkpath1;
        $data['full_linkpath2'] = $s->stylometricanalysis_full_linkpath2; 
        $data['config_array'] = json_decode($s->stylometricanalysis_json_config_array);
        $data['date'] = $s->stylometricanalysis_date;

        return $data;
    }

    public function deleteDatabaseEntry($page_title_with_namespace) {

        $dbw = wfGetDB(DB_MASTER);

        $dbw->delete(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_new_page_url' => $page_title_with_namespace //conditions
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('stylometricanalysis-error-database');
        }

        return true;
    }

}
