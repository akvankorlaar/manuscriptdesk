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
 * Todo: Exceptions should have a database exception type, and should indicate the problem in the exception message. Users should not be able to see this message
 */
class StylometricAnalysisWrapper {

    private $user_name;
    private $minimum_pages_per_collection;
    private $initial_stylometricanalysis_dir;
    private $time;
    private $full_outputpath1;
    private $full_outputpath2;
    private $config_array;

    //class constructor
    public function __construct(string $user_name, $minimum_pages_per_collection = 0, $time = 0, array $config_array, string $full_outputpath1 = '', string $full_outputpath2 = '') {
        $this->user_name = $user_name;
        $this->minimum_pages_per_collection = $minimum_pages_per_collection;
        $this->time = $time;
        $this->full_outputpath1 = $full_outputpath1;
        $this->full_outputpath2 = $full_outputpath2;
        $this->config_array = $config_array;
        $this->initial_stylometricanalysis_dir = 'initialStylometricAnalysis';
    }

    /**
     * This function checks if any uploaded manuscripts are part of a larger collection of manuscripts by retrieving data from the 'manuscripts' table
     */
    public function checkForManuscriptCollections() {

        $user_name = $this->user_name;
        $minimum_pages_per_collection = $this->minimum_pages_per_collection;
        $dbr = wfGetDB(DB_SLAVE);
        $collection_urls = array();

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

        return $collection_urls;
    }

    /**
     * This function checks if values should be removed from the 'tempstylometricanalysis' table
     */
    public function clearOldPystylOutput() {

        global $wgStylometricAnalysisOptions;

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;
        $current_time = $this->time;
        $time_array = array();
        $fulloutputpath1_array = array();
        $fulloutputpath2_array = array();
        $hours_before_delete = $wgStylometricAnalysisOptions['tempstylometricanalysis_hours_before_delete'];

        //Database query
        $res = $dbr->select(
            'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time', //values
          'tempstylometricanalysis_user',
          'tempstylometricanalysis_fulloutputpath1',
          'tempstylometricanalysis_fulloutputpath2',
            ), array(
          'tempstylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__, array(
          'ORDER BY' => 'tempstylometricanalysis_time',
            )
        );

        //while there are still titles in this query
        while ($s = $res->fetchObject()) {

            $time_array[] = $s->tempstylometricanalysis_time;
            $fulloutputpath1_array[] = $s->tempstylometricanalysis_fulloutputpath1;
            $fulloutputpath2_array[] = $s->tempstylometricanalysis_fulloutputpath2;
        }

        foreach ($time_array as $index => $old_time) {

            if ($current_time - $old_time > ($hours_before_delete * 3600)) {

                $old_full_outputpath1 = $fulloutputpath1_array[$index];
                $old_full_outputpath2 = $fulloutputpath2_array[$index];

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

    public function storeTempStylometricAnalysis() {

        $dbw = wfGetDB(DB_MASTER);

        $user_name = $this->user_name;
        $time = $this->time;
        $full_outputpath1 = $this->full_outputpath1;
        $full_outputpath2 = $this->full_outputpath2;
        $config_array = $this->config_array;

        $dbw->insert(
            'tempstylometricanalysis', //select table
            array(//insert values
          'tempstylometricanalysis_time' => $time,
          'tempstylometricanalysis_user' => $user_name,
          'tempstylometricanalysis_fulloutputpath1' => $full_outputpath1,
          'tempstylometricanalysis_fulloutputpath2' => $full_outputpath2,
          'tempstylometricanalysis_config_array' => $config_array,
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function transferDataFromTempstylometricanalysisTableToStylometricanalysistable() {

        $dbr = wfGetDB(DB_SLAVE);

        $user_name = $this->user_name;
        $time = $this->time;

        //Database query
        $res = $dbr->select(
           'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time',
          'tempstylometricanalysis_user',
          'tempstylometricanalysis_fulloutputpath1',
          'tempstylometricanalysis_fulloutputpath2',
          'tempstylometricanalysis_config_array',
            ), array(
          'tempstylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
          'tempstylometricanalysis_time =' . $dbr->addQuotes($time),
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \UnexpectedvalueException('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();

        $full_outputpath1 = $s->tempstylometricanalysis_fulloutputpath1;
        $full_outputpath2 = $s->tempstylometricanalysis_fulloutputpath2;
        $config_array = $s->tempstylometricanalysis_config_array;

        $dbw = wfGetDB(DB_MASTER);

        $dbw->insert(
            'stylometricanalysis', //select table
            array(//insert values
          'stylometricanalysis_time' => $time,
          'stylometricanalysis_user' => $user_name,
          'stylometricanalysis_fulloutputpath1' => $full_outputpath1,
          'stylometricanalysis_fulloutputpath2' => $full_outputpath2,
          'stylometricanalysis_config_array' => $config_array,
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('stylometricanalysis-error-database');
        }

        return true;
    }

}
