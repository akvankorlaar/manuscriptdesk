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
 */
class ManuscriptDeskBaseTextProcessor {
    
    public function __construct() {
        
    }

    public function getAllTextsForOneCollection(array $single_collection_data) {
        $all_texts_for_one_collection = "";
        foreach ($single_collection_data as $index => $single_manuscript_url) {
            if ($index !== 'collection_name') {
                $single_page_text = $this->getFilteredSinglePageText($single_manuscript_url);
                $all_texts_for_one_collection .= $single_page_text;
            }
        }

        $this->checkIfTextIsNotOnlyWhitespace($all_texts_for_one_collection);
        return $all_texts_for_one_collection;
    }
    
    public function getSinglePageText($single_page_manuscript_url){
        $title_object = $this->getTitleObjectExistingPage($single_page_manuscript_url);
        $wikipage = Wikipage::factory($title_object);
        return $wikipage->getText();
    }

    public function getFilteredSinglePageText($single_manuscript_url) {
        $page_text = $this->getSinglePageText($single_manuscript_url);
        $filtered_raw_text = $this->filterText($page_text);
        $this->checkIfTextIsNotOnlyWhitespace($page_text);
        return $filtered_raw_text;
    }

    private function checkIfTextIsNotOnlyWhitespace($text) {
        if (ctype_space($text) || $text === '') {
            throw new Exception('error-notextonwikipage');
        }
    }

    /**
     * This function filters out tags, and text in between certain tags. It also trims the text, and adds a single space to the last charachter if needed 
     */
    private function filterText($raw_text) {

        //filter out the following tags, and all text in between the tags
        //pagemetatable tag
        $raw_text = preg_replace('/<pagemetatable>[^<]+<\/pagemetatable>/i', '', $raw_text);

        //del tag
        $raw_text = preg_replace('/<del>[^<]+<\/del>/i', '', $raw_text);

        //note tag
        $raw_text = preg_replace('/<note>[^<]+<\/note>/i', '', $raw_text);

        //filter out any other tags, but keep all text in between the tags
        $raw_text = strip_tags($raw_text);

        $raw_text = trim($raw_text);

        //check if it is possible to get the last charachter of the page
        if (substr($raw_text, -1) !== false) {
            $last_charachter = substr($raw_text, -1);

            if ($last_charachter !== '-') {
                //If the last charachter of the current page is '-', this may indicate that the first word of the next page 
                //is linked to the last word of this page because they form a single word. In other cases, add a space after the last charachter of the current page 
                $raw_text = $raw_text . ' ';
            }
        }

        return $raw_text;
    }

    private function getTitleObjectExistingPage($single_manuscript_url) {
        $title = Title::newFromText($single_manuscript_url);

        if (!$title->exists()) {
            throw new \Exception('error-titledoesnotexist');
        }

        return $title;
    }

}
