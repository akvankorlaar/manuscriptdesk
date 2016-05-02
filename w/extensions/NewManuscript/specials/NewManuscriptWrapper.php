<?php

/**
 * This file is part of the NewManuscript extension
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

    private $alphabetnumbers_wrapper;
    private $signature_wrapper;
    
    public function __construct(AlphabetNumbersWrapper $alphabetnumbers_wrapper, SignatureWrapper $signature_wrapper) {
        $this->alphabetnumbers_wrapper = $alphabetnumbers_wrapper;
        $this->signature_wrapper = $signature_wrapper;
    }
    
    public function setUserName($user_name){
        if(isset($this->user_name)){
            return;
        }
        
        return $this->user_name = $user_name; 
    }

    public function getNumberOfUploadsForCurrentUser() {

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

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

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

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

        if (!isset($this->user_name)) {
            throw new \Exception('error-request');
        }

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
    public function storeManuscripts($posted_manuscript_title, $posted_collection_title, $user_name, $new_page_url, $date) {

        $datesort_date = date('YmdHis');
        $lowercase_title = strtolower($posted_manuscript_title);
        $lowercase_collection = strtolower($posted_collection_title);

        $dbw = wfGetDB(DB_MASTER);

        $dbw->insert('manuscripts', //select table
            array(//insert values
          'manuscripts_id' => null,
          'manuscripts_title' => $posted_manuscript_title,
          'manuscripts_user' => $user_name,
          'manuscripts_url' => $new_page_url,
          'manuscripts_date' => $date,
          'manuscripts_lowercase_title' => $lowercase_title,
          'manuscripts_collection' => $posted_collection_title,
          'manuscripts_lowercase_collection' => $lowercase_collection,
          'manuscripts_datesort' => $datesort_date,
            ), __METHOD__, 'IGNORE');
        if (!$dbw->affectedRows()) {
            throw new Exception('error-database-manuscripts');
        }

        return;
    }

    /**
     * This function insert data into the collections table
     */
    public function storeCollections($posted_collection_title, $user_name, $date) {

        $dbw = wfGetDB(DB_MASTER);

        $posted_collection_title_lowercase = strtolower($posted_collection_title);

        $dbw->insert('collections', //select table
            array(//insert values
          'collections_title' => $posted_collection_title,
          'collections_title_lowercase' => $posted_collection_title_lowercase,
          'collections_user' => $user_name,
          'collections_date' => $date,
            ), __METHOD__, 'IGNORE'); //ensures that duplicate $collection_name is ignored
        //if collection does not exist yet $dbw->affectedRows() will return false
        return;
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

        $s = $res->fetchObject();
        return $s->manuscripts_user;
    }

    public function getCollectionTitleFromUrl($url_with_namespace) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_collection', //values
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($url_with_namespace), //conditions
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();
        return $s->manuscripts_collection;
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

    public function getAlphabetNumbersWrapper() {
        return $this->alphabetnumbers_wrapper;
    }

    public function getSignatureWrapper() {
        return $this->signature_wrapper;
    }

}
