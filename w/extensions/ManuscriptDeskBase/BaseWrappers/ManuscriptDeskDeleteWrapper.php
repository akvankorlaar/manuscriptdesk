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
class ManuscriptDeskDeleteWrapper {

    private $alphabetnumbers_wrapper;

    public function __construct(AlphabetNumbersWrapper $alphabetnumbers_wrapper) {
        $this->alphabetnumbers_wrapper = $alphabetnumbers_wrapper;
    }

    /**
     * This function deletes the entry for $page_title in the 'manuscripts' table
     */
    public function deleteFromManuscripts($page_title_with_namespace) {

        $dbw = wfGetDB(DB_MASTER);

        $dbw->delete(
            'manuscripts', //from
            array(
          'manuscripts_url' => $page_title_with_namespace), //conditions
            __METHOD__);

        if (!$dbw->affectedRows()) {
            return false;
        }

        return true;
    }

    /**
     * This function checks if the collection is empty, and deletes the collection along with its metadata if this is the case
     */
    public function checkAndDeleteCollectionIfNeeded($collection_name) {

        $dbr = wfGetDB(DB_SLAVE);

        //First check if the collection is empty
        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_url',
            ), array(
          'manuscripts_collection = ' . $dbr->addQuotes($collection_name),
            ), __METHOD__
        );

        //If the collection is empty, delete the collection
        if ($res->numRows() !== 0) {
            return;
        }
        else {
            return $this->deleteFromCollections($collection_name);
        }
    }

    public function deleteFromCollections($collection_name) {
        $dbw = wfGetDB(DB_MASTER);

        $dbw->delete(
            'collections', //from
            array(
          'collections_title' => $collection_name //conditions
            ), __METHOD__);

        return;
    }

    /**
     * This function retrieves the page id from the 'page' table 
     */
    public function getPageId($page_title, $namespace = NS_MANUSCRIPTS) {

        $page_title = str_replace('Manuscripts:', '', $page_title);
        $page_title = str_replace('Collations:', '', $page_title);
        $page_title = str_replace('Stylometricananalysis:', '', $page_title);

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'page', //from
            array(
          'page_id', //values
            ), array(
          'page_namespace = ' . $dbr->addQuotes($namespace),
          'page_title = ' . $dbr->addQuotes($page_title),
            ), __METHOD__, array(
          'ORDER BY' => array('CAST(page_id AS UNSIGNED)', 'page_id'),
            )
        );

        //there should only be one result
        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();
        return $s->page_id;
    }

    public function deletePageFromId($page_id) {

        $dbw = wfGetDB(DB_MASTER);
        $dbw->begin(__METHOD__);
        $dbw->delete(
            'page', //from
            array(
          'page_id' => $page_id //conditions
            ), __METHOD__
        );

        if (!$dbw->affectedRows() > 0) {
            $dbw->rollback(__METHOD__);
            throw new \Exception('error-database-delete');
        }

        return;
    }

    public function deleteFromCollations($page_title_with_namespace) {

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

        return;
    }

    public function deleteFromStylometricAnalysis($page_title_with_namespace) {

        $dbw = wfGetDB(DB_MASTER);

        $dbw->delete(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_new_page_url' => $page_title_with_namespace //conditions
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new \Exception('stylometricanalysis-error-database');
        }

        return;
    }

    public function getManuscriptsLowercaseTitle($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($partial_url),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();
        return $s->manuscripts_lowercase_title;
    }

    public function getCollationsLowercaseTitle($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_main_title_lowercase',
            ), array(
          'collations_url = ' . $dbr->addQuotes($partial_url),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();
        return $s->collations_main_title_lowercase;
    }

    public function getStylometricAnalysisLowercaseTitle($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_main_title_lowercase',
            ), array(
          'stylometricanalysis_new_page_url = ' . $dbr->addQuotes($partial_url),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();
        return $s->stylometricanalysis_main_title_lowercase;
    }

    public function getAlphabetNumbersWrapper() {
        return $this->alphabetnumbers_wrapper;
    }

}
