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
 * 
 */
class SpecialCollectionTEIExport extends TEIExportBase {

    private $collection_metadata;

    public function __construct() {
        parent::__construct('CollectionTEIExport');
    }

    protected function setPageArguments() {
        $validator = objectRegistry::getInstance()->getManuscriptDeskBaseValidator();
        $request = $this->getRequest();
        $this->user_name = $validator->validateString($request->getText('username'));
        $this->title = $validator->validateString($request->getText('collection'));
        return;
    }

    protected function retrieveAndSetData() {
        $this->setCollectionMetadata();
        list($collection_urls, $collection_manuscript_titles) = $this->getCollectionDatabaseData();
        $text_processor = ObjectRegistry::getInstance()->getManuscriptDeskBaseTextProcessor();

        $page_texts = array();

        foreach ($collection_urls as $index => $url) {
            $manuscript_title = $collection_manuscript_titles[$index];
            $page_texts[$manuscript_title] = $text_processor->getUnfilteredSinglePageText($url);
        }

        $this->page_texts = $page_texts;
        return;
    }

    /**
     * Get all urls for a single collection
     * 
     * @return type array
     * @throws \Exception if no urls found
     */
    private function getCollectionDatabaseData() {
        if (!isset($this->user_name) || !isset($this->title)) {
            throw new \Exception('error-request');
        }

        $collection_name = $this->title;
        $user_name = $this->user_name;
        $dbr = wfGetDB(DB_SLAVE);
        $collection_urls = array();
        $collection_manuscript_titles = array();

        $res = $dbr->select(
            'manuscripts', array(
          'manuscripts_url',
          'manuscripts_title',
          'manuscripts_lowercase_title'
            ), array(
          'manuscripts_collection = ' . $dbr->addQuotes($collection_name),
          'manuscripts_user =' . $dbr->addQuotes($user_name),
            ), __METHOD__, array(
          'ORDER BY' => array('CAST(manuscripts_lowercase_title AS UNSIGNED)', 'manuscripts_lowercase_title'),
            )
        );

        if ($res->numRows() === 0) {
            throw new \Exception('error-request');
        }

        //while there are still titles in this query
        while ($s = $res->fetchObject()) {
            $collection_urls[] = $s->manuscripts_url;
            $collection_manuscript_titles[] = $s->manuscripts_title;
        }

        return array($collection_urls, $collection_manuscript_titles);
    }

    private function setCollectionMetadata() {
        $collection_name = $this->title;

        $dbr = wfGetDB(DB_SLAVE);
        $meta_data = array();

        $res = $dbr->select(
            'collections', //from
            array(//values
          'collections_metatitle',
          'collections_metaauthor',
          'collections_metayear',
          'collections_metapages',
          'collections_metacategory',
          'collections_metaproduced',
          'collections_metaproducer',
          'collections_metaeditors',
          'collections_metajournal',
          'collections_metajournalnumber',
          'collections_metatranslators',
          'collections_metawebsource',
          'collections_metaid',
          'collections_metanotes',
            ), array(
          'collections_title = ' . $dbr->addQuotes($collection_name),
            )
        );

        //there should only be one result
        if ($res->numRows() === 1) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {
                $meta_data ['collections_metatitle'] = $s->collections_metatitle;
                $meta_data ['collections_metaauthor'] = $s->collections_metaauthor;
                $meta_data ['collections_metayear'] = $s->collections_metayear;
                $meta_data ['collections_metapages'] = $s->collections_metapages;
                $meta_data ['collections_metacategory'] = $s->collections_metacategory;
                $meta_data ['collections_metaproduced'] = $s->collections_metaproduced;
                $meta_data ['collections_metaproducer'] = $s->collections_metaproducer;
                $meta_data ['collections_metaeditors'] = $s->collections_metaeditors;
                $meta_data ['collections_metajournal'] = $s->collections_metajournal;
                $meta_data ['collections_metajournalnumber'] = $s->collections_metajournalnumber;
                $meta_data ['collections_metatranslators'] = $s->collections_metatranslators;
                $meta_data ['collections_metawebsource'] = $s->collections_metawebsource;
                $meta_data ['collections_metaid'] = $s->collections_metaid;
                $meta_data ['collections_metanotes'] = $s->collections_metanotes;
            }
        }

        $this->collection_metadata = $meta_data;
        return;
    }

    protected function formatTEIXML() {

        if (!isset($this->page_texts) || !isset($this->title) || !isset($this->collection_metadata)) {
            throw new \Exception('error-request');
        }

        $xml = '';

        $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<TEI>';
        $xml .= '<text xml:id="' . $this->title . '">';

        $xml .= '<teiHeader>';

        foreach ($this->collection_metadata as $name => $entry) {
            $xml .= '<' . $name . '>';
            $xml .= $entry;
            $xml .= '</' . $name . '>';
        }

        $xml .= '</teiHeader>';
        $xml .= '<body>';
        $xml .= '<div>';

        foreach ($this->page_texts as $manuscript_title => $page_text) {
            $xml .= '<pb n="' . $manuscript_title . '"/>';
            $xml .= $page_text;
        }

        $xml .= '</div>';
        $xml .= '</body>';
        $xml .= '</text>';
        $xml .= '</TEI>';

        $this->TEIXML = $xml;
        return;
    }

}
