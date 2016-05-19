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
class StylometricAnalysisWrapper extends ManuscriptDeskBaseWrapper {

    private $alphabetnumbers_wrapper;
    private $signature_wrapper;

    public function __construct(AlphabetNumbersWrapper $alphabetnumbers_wrapper, SignatureWrapper $signature_wrapper) {
        $this->alphabetnumbers_wrapper = $alphabetnumbers_wrapper;
        $this->signature_wrapper = $signature_wrapper;
    }

    public function setUserName($user_name) {
        if (isset($this->user_name)) {
            return;
        }
        return $this->user_name = $user_name;
    }

    /**
     * This function checks if any uploaded manuscripts are part of a larger collection of manuscripts by retrieving data from the 'manuscripts' table
     */
    public function getManuscriptsCollectionData() {

        global $wgStylometricAnalysisOptions;

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        $minimum_pages_per_collection = $wgStylometricAnalysisOptions['minimum_pages_per_collection'];
        $minimum_collections = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];
        $dbr = wfGetDB(DB_SLAVE);
        $collection_urls = array();
        $user_name = $this->user_name;

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
          'ORDER BY' => array('CAST(manuscripts_lowercase_collection AS UNSIGNED)','manuscripts_lowercase_collection'),
            )
        );

        if ($res->numRows() > 0) {
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
            throw new \Exception('error-fewuploads');
        }

        return $collection_urls;
    }

    /**
     * This function checks if values should be removed from the 'tempstylometricanalysis' table
     */
    public function clearOldPystylOutput($time = 0) {

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        global $wgStylometricAnalysisOptions;

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;
        $time_array = array();
        $full_outputpath1_array = array();
        $full_outputpath2_array = array();
        $hours_before_delete = $wgStylometricAnalysisOptions['tempstylometricanalysis_hours_before_delete'];

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
          'ORDER BY' => array('CAST(tempstylometricanalysis_time AS UNSIGNED)','tempstylometricanalysis_time'),
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

                $this->deleteOutputImage($old_full_outputpath1);
                $this->deleteOutputImage($old_full_outputpath2);
                $this->deleteOldEntriesFromTempstylometricanalysisTable($old_time);
            }
        }

        return true;
    }

    private function deleteOutputImage($full_outputpath) {
        if (!is_file($full_outputpath)) {
            return;
        }

        unlink($full_outputpath);

        if (is_file($full_outputpath)) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        return;
    }

    private function deleteOldEntriesFromTempstylometricanalysisTable($old_time) {

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        $dbw = wfGetDB(DB_MASTER);
        $user_name = $this->user_name;

        $dbw->delete(
            'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time = ' . $dbw->addQuotes($old_time),
          'tempstylometricanalysis_user = ' . $dbw->addQuotes($user_name), //conditions
            ), __METHOD__);

        if (!$dbw->affectedRows()) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function storeTempStylometricAnalysis(array $collection_name_data, $time = 0, $new_page_url = '', $date = 0, $full_linkpath1 = '', $full_linkpath2 = '', $full_outputpath1 = '', $full_outputpath2 = '', array $pystyl_config, $main_title) {

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        $main_title_lowercase = strtolower($main_title);

        $dbw = wfGetDB(DB_MASTER);

        $user_name = $this->user_name;
        $json_collection_name_data = json_encode($collection_name_data);
        $json_pystyl_config = json_encode($pystyl_config);

        $dbw->insert(
            'tempstylometricanalysis', //select table
            array(//insert values
          'tempstylometricanalysis_time' => $time,
          'tempstylometricanalysis_user' => $user_name,
          'tempstylometricanalysis_full_outputpath1' => $full_outputpath1,
          'tempstylometricanalysis_full_outputpath2' => $full_outputpath2,
          'tempstylometricanalysis_full_linkpath1' => $full_linkpath1,
          'tempstylometricanalysis_full_linkpath2' => $full_linkpath2,
          'tempstylometricanalysis_json_pystyl_config' => $json_pystyl_config,
          'tempstylometricanalysis_json_collection_name_array' => $json_collection_name_data,
          'tempstylometricanalysis_new_page_url' => $new_page_url,
          'tempstylometricanalysis_main_title' => $main_title,
          'tempstylometricanalysis_main_title_lowercase' => $main_title_lowercase,
          'tempstylometricanalysis_date' => $date,
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function transferDataFromTempStylometricAnalysisToStylometricAnalysisTable($time) {

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        $dbr = wfGetDB(DB_SLAVE);

        $user_name = $this->user_name;

        $res = $dbr->select(
            'tempstylometricanalysis', //from
            array(
          'tempstylometricanalysis_time',
          'tempstylometricanalysis_user',
          'tempstylometricanalysis_full_outputpath1',
          'tempstylometricanalysis_full_outputpath2',
          'tempstylometricanalysis_full_linkpath1',
          'tempstylometricanalysis_full_linkpath2',
          'tempstylometricanalysis_json_pystyl_config',
          'tempstylometricanalysis_json_collection_name_array',
          'tempstylometricanalysis_new_page_url',
          'tempstylometricanalysis_main_title',
          'tempstylometricanalysis_main_title_lowercase',
          'tempstylometricanalysis_date'
            ), array(
          'tempstylometricanalysis_time =' . $dbr->addQuotes($time),
          'tempstylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();

        $full_outputpath1 = $s->tempstylometricanalysis_full_outputpath1;
        $full_outputpath2 = $s->tempstylometricanalysis_full_outputpath2;
        $full_linkpath1 = $s->tempstylometricanalysis_full_linkpath1;
        $full_linkpath2 = $s->tempstylometricanalysis_full_linkpath2;
        $json_pystyl_config = $s->tempstylometricanalysis_json_pystyl_config;
        $json_collection_name_data = $s->tempstylometricanalysis_json_collection_name_array;
        $new_page_url = $s->tempstylometricanalysis_new_page_url;
        $main_title = $s->tempstylometricanalysis_main_title;
        $main_title_lowercase = $s->tempstylometricanalysis_main_title_lowercase;
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
          'stylometricanalysis_json_pystyl_config' => $json_pystyl_config,
          'stylometricanalysis_json_collection_name_array' => $json_collection_name_data,
          'stylometricanalysis_new_page_url' => $new_page_url,
          'stylometricanalysis_main_title' => $main_title,
          'stylometricanalysis_main_title_lowercase' => $main_title_lowercase,
          'stylometricanalysis_date' => $date,
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        return true;
    }

    public function getStylometricAnalysisNewPageData($time) {

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

        $dbr = wfGetDB(DB_SLAVE);
        $user_name = $this->user_name;

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_time',
          'stylometricanalysis_user',
          'stylometricanalysis_new_page_url',
          'stylometricanalysis_main_title_lowercase',
            ), array(
          'stylometricanalysis_time =' . $dbr->addQuotes($time),
          'stylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();
        $new_page_url = $s->stylometricanalysis_new_page_url;
        $main_title_lowercase = $s->stylometricanalysis_main_title_lowercase;
        return array($new_page_url, $main_title_lowercase);
    }

    public function getStylometricanalysisData($url_with_namespace) {

        $dbr = wfGetDB(DB_SLAVE);
        $data = array();

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_time',
          'stylometricanalysis_user',
          'stylometricanalysis_full_outputpath1',
          'stylometricanalysis_full_outputpath2',
          'stylometricanalysis_full_linkpath1',
          'stylometricanalysis_full_linkpath2',
          'stylometricanalysis_json_pystyl_config',
          'stylometricanalysis_json_collection_name_array',
          'stylometricanalysis_new_page_url',
          'stylometricanalysis_date',
            ), array(
          'stylometricanalysis_new_page_url = ' . $dbr->addQuotes($url_with_namespace),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        $s = $res->fetchObject();

        $data['time'] = $s->stylometricanalysis_time;
        $data['user'] = $s->stylometricanalysis_user;
        $data['full_outputpath1'] = $s->stylometricanalysis_full_outputpath1;
        $data['full_outputpath2'] = $s->stylometricanalysis_full_outputpath2;
        $data['full_linkpath1'] = $s->stylometricanalysis_full_linkpath1;
        $data['full_linkpath2'] = $s->stylometricanalysis_full_linkpath2;
        $data['pystyl_config'] = (array) json_decode($s->stylometricanalysis_json_pystyl_config);
        $data['collection_name_data'] = (array) json_decode($s->stylometricanalysis_json_collection_name_array);
        $data['date'] = $s->stylometricanalysis_date;

        return $data;
    }

    public function getAlphabetNumbersWrapper() {
        return $this->alphabetnumbers_wrapper;
    }

    public function getSignatureWrapper() {
        return $this->signature_wrapper;
    }

}
