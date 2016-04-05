<?php

/**
 * This file is part of the Collate extension
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

class ManuscriptDeskAllDataDeleteWrapper extends ManuscriptDeskBaseWrapper{
       
    public function deleteManuscriptDeskData() {

        $res_collations = $this->getManuscriptsData();

        if ($res_collations->numRows() > 0) {
            while ($s = $res_collations->fetchObject()) {
                $user_name = $s->manuscripts_user;
                $manuscripts_title = $s->manuscripts_title;
                $collection_title = $s->manuscripts_collection;
                $manuscripts_url = $s->manuscripts_url;

                $paths = new NewManuscriptPaths($user_name, $manuscripts_title);
                $paths->setExportPaths();
                $paths->setPartialUrl();

                $delete_wrapper = new ManuscriptDeskDeleteWrapper();
                $deleter = new ManuscriptDeskDeleter($delete_wrapper, $paths, $collection_title, $manuscripts_url);
                $deleter->execute();
            }
        }

        $res_collations = $this->getAllCollationsUrls();
        $this->deleteAllPagesFromDatabaseResult($res_collations, 'collations_url');
        $res_stylometricanalysis = $this->getAllStylometricAnalysisUrls();
        $this->deleteAllPagesFromDatabaseResult($res_stylometricanalysis, 'stylometricanalysis_url');

        return;
    }

    public function deleteAllPagesFromDatabaseResult($res, $result_name) {

        if ($res->numRows() > 0) {
            while ($s = $res->fetchObject()) {
                $delete_wrapper = new ManuscriptDeskDeleteWrapper();
                $page_id = $delete_wrapper->getPageId($collations_url);
                $delete_wrapper->deletePageFromId($page_id);
            }
        }

        return;
    }

    private function getManuscriptsData() {
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
          'ORDER BY' => 'manuscripts_lowercase_title',
            )
        );

        return $res;
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
          'ORDER BY' => 'collations_url',
            )
        );

        return $res;
    }

    private function getAllStylometricAnalysisUrls() {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_url',
            ), array(
            )
            , __METHOD__, array(
          'ORDER BY' => 'stylometricanalysis_url',
            )
        );

        return $res;
    }

}
