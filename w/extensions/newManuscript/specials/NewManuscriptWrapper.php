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
class NewManuscriptWrapper extends ManuscriptDeskBaseWrapper {

    public function getNumberOfUploadsForCurrentUser() {

        $dbr = wfGetDB(DB_SLAVE);
        $number_of_uploads = 0;

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
            ), array(
          'manuscripts_user = ' . $dbr->addQuotes($this->user_name), //conditions
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
            )
        );

        while ($s = $res->fetchObject()) {
            $number_of_uploads +=1;
        }

        return $number_of_uploads;
    }

    /**
     * This function retrieves the collections of the current user
     */
    public function getCollectionsCurrentUser() {

        $dbr = wfGetDB(DB_SLAVE);

        $user_name = $this->user_name;
        $collections_current_user = array();

        $res = $dbr->select(
            'collections', //from
            array(
          'collections_title', //values
          'collections_title_lowercase',
            ), array(
          'collections_user = ' . $dbr->addQuotes($user_name), //conditions
            ), __METHOD__, array(
          'ORDER BY' => 'collections_title_lowercase',
            )
        );

        while ($s = $res->fetchObject()) {
            $collections_current_user[] = $s->collections_title;
        }

        return $collections_current_user;
    }

    public function checkWhetherCurrentUserIsTheOwnerOfTheCollection($posted_collection_title) {
        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'collections', //from
            array(//values
          'collections_title',
          'collections_user',
            ), array(
          'collections_title = ' . $dbr->addQuotes($posted_collection_title),
            ), __METHOD__, array(
          'ORDER BY' => 'collections_title',
            )
        );

        if ($res->numRows() === 1) {
            $s = $res->fetchObject();
            $collections_user = $s->collections_user;

            //if the user is not the owner of the collection, throw an exception
            if ($collections_user !== $this->user_name) {
                throw new \Exception('newmanuscript-error-notcollectionsuser');
            }
        }

        //current collection does not exist yet
        return;
    }

    /**
     * This functions checks if the collection already reached the maximum allowed manuscript pages, or if the current user is the creator of the collection
     */
    public function checkCollectionDoesNotExceedMaximumPages($posted_collection_title) {

        global $wgNewManuscriptOptions;
        $maximum_pages_per_collection = $wgNewManuscriptOptions['maximum_pages_per_collection'];
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_url', //values
            ), array(
          'manuscripts_user = ' . $dbr->addQuotes($this->user_name), //conditions
          'manuscripts_collection = ' . $dbr->addQuotes($posted_collection_title),
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
            )
        );

        if ($res->numRows() > $maximum_pages_per_collection) {
            throw new \Exception('newmanuscript-error-collectionmaxreached');
        }

        return;
    }

    /**
     * This function insert data into the manuscripts table
     */
    public function storeManuscripts($posted_title, $collection, $user_name, $new_page_url, $date) {

        $date2 = date('YmdHis');
        $lowercase_title = strtolower($posted_title);
        $lowercase_collection = strtolower($collection);

        $dbw = wfGetDB(DB_MASTER);

        $dbw->insert('manuscripts', //select table
            array(//insert values
          'manuscripts_id' => null,
          'manuscripts_title' => $posted_title,
          'manuscripts_user' => $user_name,
          'manuscripts_url' => $new_page_url,
          'manuscripts_date' => $date,
          'manuscripts_lowercase_title' => $lowercase_title,
          'manuscripts_collection' => $collection,
          'manuscripts_lowercase_collection' => $lowercase_collection,
          'manuscripts_datesort' => $date2,
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
     * This function insert data into the collections table
     */
    public function storeCollections($collection_name, $user_name, $date) {

        $dbw = wfGetDB(DB_MASTER);

        $collections_title_lowercase = strtolower($collection_name);

        $dbw->insert('collections', //select table
            array(//insert values
          'collections_title' => $collection_name,
          'collections_title_lowercase' => $collections_title_lowercase,
          'collections_user' => $user_name,
          'collections_date' => $date,
            ), __METHOD__, 'IGNORE'); //ensures that duplicate $collection_name is ignored

        if ($dbw->affectedRows()) {
            //collection did not exist yet
            return true;
        }
        else {
            //collection already exists
            return false;
        }
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

    private function deleteFromCollections($collection_name) {
        $dbw = wfGetDB(DB_MASTER);

        $dbw->delete(
            'collections', //from
            array(
          'collections_title' => $collection_name //conditions
            ), __METHOD__);
    }

    public function getManuscriptsTitleFromUrl($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($partial_url), //conditions
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        return $res->fetchObject()->manuscripts_title;
    }

    public function getUserNameFromUrl($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_user', //values
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($partial_url), //conditions
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        return $res->fetchObject()->manuscripts_user;
    }

    public function getCollectionTitleFromUrl($url_without_namespace) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_collection', //values
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($url_without_namespace), //conditions
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        return $res->fetchObject()->manuscripts_collection;
    }

    public function getPreviousAndNextPageUrl($collection_title, $partial_url) {
        $dbr = wfGetDB(DB_SLAVE);
        $no_previous_page = false;
        $previous_page_url = null;
        $next_page_url = null;

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_url', //values
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_collection = ' . $dbr->addQuotes($collection_title), //conditions
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
            )
        );

        while ($s = $res->fetchObject()) {

            //once the current page has been found in the database
            if ($s->manuscripts_url === $partial_url) {

                //set the last entry to $previous_page_url, if it exists
                if (isset($previous_url)) {
                    $previous_page_url = $previous_url;
                    continue;
                }
                else {
                    $no_previous_page = true;
                    continue;
                }
            }

            //once $previous_page_url has been set, or if there is $no_previous_page, set the $next_page_url
            if (isset($previous_page_url) || $no_previous_page === true) {
                $next_page_url = $s->manuscripts_url;
                break;
            }
            //otherwise, the current page has not been found yet  
            else {
                $previous_url = $s->manuscripts_url;
            }
        }

        return array($previous_page_url, $next_page_url);
    }

}
