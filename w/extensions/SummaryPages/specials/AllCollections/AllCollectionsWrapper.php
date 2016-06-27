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
class AllCollectionsWrapper implements SummaryPageWrapperInterface {

    private $alphabetnumbers_wrapper;
    private $signature_wrapper;
    private $user_name;

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

    public function getData($offset, $button_name = '', $next_letter_alphabet = '') {

        global $wgNewManuscriptOptions;
        $max_on_page = $wgNewManuscriptOptions['max_on_page'];
        $dbr = wfGetDB(DB_SLAVE);

        if (isset($this->user_name)) {
            $conditions = array(
              'collections_user = ' . $dbr->addQuotes($this->user_name),
              'collections_title != ' . $dbr->addQuotes(""),
              'collections_title != ' . $dbr->addQuotes("none"),
            );
        }
        else {
            $conditions = array(
              'collections_title_lowercase >= ' . $dbr->addQuotes($button_name),
              'collections_title_lowercase < ' . $dbr->addQuotes($next_letter_alphabet),
              'collections_title_lowercase != ' . $dbr->addQuotes("none"),
            );
        }

        $collection_titles = array();
        $next_offset = null;

        $res = $dbr->select(
            'collections', //from
            array(
          'collections_title', //values
          'collections_title_lowercase',
          'collections_user',
          'collections_date',
            ), $conditions, __METHOD__, array(
          'ORDER BY' => array('CAST(collections_title_lowercase AS UNSIGNED)','collections_title_lowercase'),
          'LIMIT' => $max_on_page + 1,
          'OFFSET' => $offset,
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //add titles to the title array as long as it is not bigger than max_on_page
                if (count($collection_titles) < $max_on_page) {

                    $collection_titles[] = array(
                      'collections_title' => $s->collections_title,
                      'collections_user' => $s->collections_user,
                      'collections_date' => $s->collections_date,
                    );
                }
                //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
                else {
                    $next_offset = ($offset) + ($max_on_page);
                    break;
                }
            }
        }

        return array($collection_titles, $next_offset);
    }

    /**
     * This function retrieves all the data for a single collection
     */
    public function getSingleCollectionData($collection_title) {
        $meta_data = $this->getSingleCollectionMetadata($collection_title);
        $pages_within_collection = $this->getSingleCollectionPages($collection_title);
        return array($meta_data, $pages_within_collection);
    }

    public function getSingleCollectionMetadata($collection_title) {
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
          'collections_title = ' . $dbr->addQuotes($collection_title),
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

        return $meta_data;
    }

    public function getSingleCollectionPages($collection_title) {

        $pages_within_collection = array();
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
          'manuscripts_url',
          'manuscripts_date',
          'manuscripts_signature',
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_collection = ' . $dbr->addQuotes($collection_title),
            ), __METHOD__, array(
          'ORDER BY' => array('CAST(manuscripts_lowercase_title AS UNSIGNED)',
            'manuscripts_lowercase_title'),
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                $pages_within_collection[] = array(
                  'manuscripts_title' => $s->manuscripts_title,
                  'manuscripts_url' => $s->manuscripts_url,
                  'manuscripts_date' => $s->manuscripts_date,
                  'manuscripts_signature' => $s->manuscripts_signature,
                );
            }
        }

        return $pages_within_collection;
    }

    /**
     * This function inserts data into the 'collections' table 
     */
    public function updateCollectionsMetadata($saved_metadata, $collection_title) {

        $user_name = $this->user_name;

        $metatitle = isset($saved_metadata['wpmetadata_title']) ? $saved_metadata['wpmetadata_title'] : '';
        $metaauthor = isset($saved_metadata['wpmetadata_author']) ? $saved_metadata['wpmetadata_author'] : '';
        $metayear = isset($saved_metadata['wpmetadata_year']) ? $saved_metadata['wpmetadata_year'] : '';
        $metapages = isset($saved_metadata['wpmetadata_pages']) ? $saved_metadata['wpmetadata_pages'] : '';
        $metacategory = isset($saved_metadata['wpmetadata_category']) ? $saved_metadata['wpmetadata_category'] : '';
        $metaproduced = isset($saved_metadata['wpmetadata_produced']) ? $saved_metadata['wpmetadata_produced'] : '';
        $metaproducer = isset($saved_metadata['wpmetadata_producer']) ? $saved_metadata['wpmetadata_producer'] : '';
        $metaeditors = isset($saved_metadata['wpmetadata_editors']) ? $saved_metadata['wpmetadata_editors'] : '';
        $metajournal = isset($saved_metadata['wpmetadata_journal']) ? $saved_metadata['wpmetadata_journal'] : '';
        $metajournalnumber = isset($saved_metadata['wpmetadata_journalnumber']) ? $saved_metadata['wpmetadata_journalnumber'] : '';
        $metatranslators = isset($saved_metadata['wpmetadata_translators']) ? $saved_metadata['wpmetadata_translators'] : '';
        $metawebsource = isset($saved_metadata['wpmetadata_websource']) ? $saved_metadata['wpmetadata_websource'] : '';
        $metaid = isset($saved_metadata['wpmetadata_id']) ? $saved_metadata['wpmetadata_id'] : '';
        $metanotes = isset($saved_metadata['wpmetadata_notes']) ? $saved_metadata['wpmetadata_notes'] : '';

        $dbw = wfGetDB(DB_MASTER);

        $dbw->update('collections', //select table
            array(//update values
          'collections_metatitle' => $metatitle,
          'collections_metaauthor' => $metaauthor,
          'collections_metayear' => $metayear,
          'collections_metapages' => $metapages,
          'collections_metacategory' => $metacategory,
          'collections_metaproduced' => $metaproduced,
          'collections_metaproducer' => $metaproducer,
          'collections_metaeditors' => $metaeditors,
          'collections_metajournal' => $metajournal,
          'collections_metajournalnumber' => $metajournalnumber,
          'collections_metatranslators' => $metatranslators,
          'collections_metawebsource' => $metawebsource,
          'collections_metaid' => $metaid,
          'collections_metanotes' => $metanotes,
            ), array(
          'collections_user  = ' . $dbw->addQuotes($user_name), //conditions
          'collections_title = ' . $dbw->addQuotes($collection_title),
            ), //conditions
            __METHOD__, 'IGNORE');

        //no affected rows means nothing to update, so always return true
        return true;
    }

    /**
     * Update the manuscripts table and insert new values when title change
     * 
     * @param type string $manuscript_new_title
     * @param type string $new_page_url
     * @param type string $manuscript_url_old_title
     * @return type void
     * @throws \Exception if insert unsuccessfull
     */
    public function updateManuscriptsTable($manuscript_new_title, $new_page_url, $manuscript_url_old_title) {
        $dbw = wfGetDB(DB_MASTER);
        $dbw->begin(__METHOD__);

        $dbw->update(
            'manuscripts', //select table
            array(
          'manuscripts_title' => $manuscript_new_title, //update values
          'manuscripts_url' => $new_page_url,
          'manuscripts_lowercase_title' => strtolower($manuscript_new_title),
            ), array(
          'manuscripts_url  = ' . $dbw->addQuotes($manuscript_url_old_title), //conditions
            ), __METHOD__, 'IGNORE'
        );

        if (!$dbw->affectedRows()) {
            $dbw->rollback(__METHOD__);
            throw new \Exception('error-database-update');
        }

        return;
    }

    public function getAlphabetNumbersWrapper() {
        return $this->alphabetnumbers_wrapper;
    }

    public function getSignatureWrapper() {
        return $this->signature_wrapper;
    }

}
