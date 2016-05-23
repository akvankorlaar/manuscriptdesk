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
class HelperScriptsDeleteWrapper {

    private $delete_wrapper;

    public function __construct(ManuscriptDeskDeleteWrapper $delete_wrapper) {
        $this->delete_wrapper = $delete_wrapper;
    }

    /**
     * Delete all data created by users in the manuscript desk 
     */
    public function deleteManuscriptDeskData() {
        $res_manuscripts = $this->getAllManuscriptsData();
        $this->deleteManuscriptPages($res_manuscripts);
        $res_collations = $this->getAllCollationsUrls();
        $this->deleteAllPagesFromDatabaseResult($res_collations, 'collations_url', NS_COLLATIONS);
        $res_stylometricanalysis = $this->getAllStylometricAnalysisUrls();
        return $this->deleteAllPagesFromDatabaseResult($res_stylometricanalysis, 'stylometricanalysis_new_page_url', NS_STYLOMETRICANALYSIS);
    }

    private function deleteManuscriptPages($res) {

        if ($res->numRows() > 0) {
            while ($s = $res->fetchObject()) {
                $user_name = $s->manuscripts_user;
                $manuscripts_title = $s->manuscripts_title;
                $collection_title = $s->manuscripts_collection;
                $manuscripts_url = $s->manuscripts_url;

                $paths = new NewManuscriptPaths($user_name, $manuscripts_title);
                $paths->setExportPaths();
                $paths->setPartialUrl();

                $deleter = new ManuscriptDeskDeleter($this->delete_wrapper, $paths, $collection_title, $manuscripts_url);
                $deleter->deleteManuscriptPage();
            }
        }

        return;
    }

    private function deleteAllPagesFromDatabaseResult($res, $result_name, $namespace) {

        $delete_wrapper = $this->delete_wrapper;

        if ($res->numRows() > 0) {
            while ($s = $res->fetchObject()) {
                $partial_url = $s->$result_name;

                if ($namespace === NS_COLLATIONS) {
                    $delete_wrapper->deleteFromCollations($partial_url);
                }
                elseif ($namespace === NS_STYLOMETRICANALYSIS) {
                    $delete_wrapper->deleteFromStylometricAnalysis($partial_url);
                }

                $page_id = $delete_wrapper->getPageId($partial_url, $namespace);
                $delete_wrapper->deletePageFromId($page_id);
            }
        }

        return;
    }

    private function getAllCollationsUrls() {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_url',
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => array('CAST(collations_url AS UNSIGNED)', 'collations_url'),
            )
        );

        return $res;
    }

    private function getAllStylometricAnalysisUrls() {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_new_page_url',
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => array('CAST(stylometricanalysis_new_page_url AS UNSIGNED)', 'stylometricanalysis_new_page_url'),
            )
        );

        return $res;
    }

    private function getAllManuscriptsData() {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title',
          'manuscripts_user',
          'manuscripts_collection',
          'manuscripts_url',
          'manuscripts_lowercase_title', //values
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => array('CAST(manuscripts_lowercase_title AS UNSIGNED)', 'manuscripts_lowercase_title'),
            )
        );

        return $res;
    }

}
