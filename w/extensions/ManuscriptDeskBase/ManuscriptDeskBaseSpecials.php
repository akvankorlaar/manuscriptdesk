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
 * 
 * Idea: Refractor code and use interfaces. For example, there is some duplication in the execute and handleErrors methods
 */
class ManuscriptDeskBaseSpecials extends SpecialPage {

    public function __construct($page_name) {
        parent::__construct($page_name);
    }

    /**
     * This function checks if the edit token was posted
     */
    protected function tokenWasPosted() {
        $edit_token = $this->getEditToken();
        if ($edit_token === '') {
            return false;
        }

        return true;
    }

    /**
     * This function gets the edit token
     */
    protected function getEditToken() {
        $request = $this->getRequest();
        return $request->getText('wpEditToken');
    }

    /**
     * This function checks the edit token
     */
    protected function checkEditToken() {
        $edit_token = $this->getEditToken();
        if ($this->getUser()->matchEditToken($edit_token) === false) {
            throw new \Exception('error-edittoken');
        }

        return true;
    }

    /**
     * This function checks if the user has the appropriate permissions
     */
    protected function checkManuscriptDeskPermission() {
        $out = $this->getOutput();
        $user_object = $out->getUser();

        if (!in_array('ManuscriptEditors', $user_object->getGroups())) {
            throw new \Exception('error-nopermission');
        }

        return true;
    }

    /**
     * This function checks if a request was posted
     */
    protected function requestWasPosted() {

        $request = $this->getRequest();

        if (!$request->wasPosted()) {
            return false;
        }

        return true;
    }

    /**
     * This function retrieves the wiki text from a page
     */
    protected function getSinglePageText(Title $title) {

        $article_object = Wikipage::factory($title);
        $raw_text = $article_object->getRawText();

        $filtered_raw_text = $this->filterText($raw_text);

        return $filtered_raw_text;
    }

    /**
     * This function filters out tags, and text in between certain tags. It also trims the text, and adds a single space to the last charachter if needed 
     */
    protected function filterText($raw_text) {

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

    protected function createNewWikiPage($new_url) {

        $title_object = Title::newFromText($new_url);
        $local_url = $title_object->getLocalURL();
        $context = $this->getContext();
        $article = Article::newFromTitle($title_object, $context);

        $editor_object = new EditPage($article);
        $content_new = new wikitextcontent('<!--' . $this->msg('newmanuscript-newpage') . '-->');
        $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97, false, null, $editor_object->contentFormat);

        if (!$doEditStatus->isOK()) {
            throw new \Exception('error-newpage');
            //$errors = $doEditStatus->getErrorsArray();
        }

        return $local_url;
    }

    /**
     * Check if form 1 was posted
     */
    protected function form1WasPosted() {
        $request = $this->getRequest();
        if ($request->getText('form1Posted') !== '') {
            return true;
        }

        return false;
    }

}
