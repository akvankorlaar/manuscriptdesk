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
abstract class TEIExportBase extends SpecialPage {
    
    public $usage = '
    
     Usage <br>
     Example: Special:CollectionTEIExport?username=Username&collection=CollectionName <br>
     Example: Special:ManuscriptTEIExport?username=Username&manuscript=ManuscriptName <br>
     '; 

    /**
     * User name of the current user 
     */
    protected $user_name;

    /**
     * Unfiltered TEI Page texts of manuscript or entire collection 
     */
    protected $page_texts;

    /**
     * TEIXML constructed from the page text data, and metadata in case of collections 
     */
    protected $TEIXML;

    public function __construct($page_name) {
        parent::__construct($page_name);
    }

    public function execute($subpage_arguments) {
        try {
            $this->preventMediaWikiFromOutputtingSkin();
            $this->checkUserIsManuscriptEditor();
            $this->setPageArguments();
            $this->retrieveAndSetData();
            $this->formatTEIXML();
            $this->outputTEIXML();
        } catch (Exception $e) {
            $response = $this->getRequest()->response();
            $this->getOutput()->addHTML($this->usage); 
            return;
        }
    }

    protected function preventMediaWikiFromOutputtingSkin() {
        $out = $this->getOutput();
        $out->setArticleBodyOnly(true);
        return;
    }

    protected function checkUserIsManuscriptEditor() {
        $user = $this->getUser();

        if (!in_array('ManuscriptEditors', $user->getGroups())) {
            throw new \Exception('error-nopermission');
        }

        $this->user_name = $user->getName();
        return;
    }

    abstract protected function setPageArguments();

    abstract protected function retrieveAndSetData();

    abstract protected function formatTEIXML();

    abstract protected function outputTEIXML();
}
