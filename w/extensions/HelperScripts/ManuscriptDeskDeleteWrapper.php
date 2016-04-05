<?php

/**
 * This file is part of the NewManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */
class ManuscriptDeskDeleteWrapper {

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

        return;
    }

    /**
     * This function retrieves the page id from the 'page' table 
     */
    public function getPageId($page_title) {

        $page_title = str_replace('Manuscripts:', '', $page_title);

        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'page', //from
            array(
          'page_id', //values
            ), array(
          'page_namespace = ' . $dbr->addQuotes(NS_MANUSCRIPTS),
          'page_title = ' . $dbr->addQuotes($page_title),
            ), __METHOD__, array(
          'ORDER BY' => 'page_id',
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

}
