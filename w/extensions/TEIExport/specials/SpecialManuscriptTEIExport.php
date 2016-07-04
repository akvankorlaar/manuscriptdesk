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
class SpecialManuscriptTEIExport extends TEIExportBase {

    private $manuscript_title;

    public function __construct() {
        parent::__construct('ManuscriptTEIExport');
    }

    protected function setPageArguments() {
        $validator = objectRegistry::getInstance()->getManuscriptDeskBaseValidator();
        $request = $this->getRequest();
        $this->user_name = $validator->validateString($request->getText('username'));
        $this->manuscript_title = $validator->validateString($request->getText('manuscript'));
        return;
    }

    protected function retrieveAndSetData() {
        list($manuscript_url, $manuscript_title) = $this->getManuscriptsDatabaseData();
        $text_processor = ObjectRegistry::getInstance()->getManuscriptDeskBaseTextProcessor();
        $page_texts[$manuscript_title] = $text_processor->getUnfilteredSinglePageText($manuscript_url);
        $this->page_texts = $page_texts;
        return;
    }

    /**
     * Get the url and the title for a single manuscript page
     * 
     * @return type array
     * @throws \Exception if no row found
     */
    private function getManuscriptsDatabaseData() {
        if (!isset($this->user_name) || !isset($this->manuscript_title)) {
            throw new \Exception('error-request');
        }

        $manuscript_title = $this->manuscript_title;
        $user_name = $this->user_name;
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', array(
          'manuscripts_url',
          'manuscripts_title',
          'manuscripts_lowercase_title'
            ), array(
          'manuscripts_title = ' . $dbr->addQuotes($manuscript_title),
          'manuscripts_user =' . $dbr->addQuotes($user_name),
            ), __METHOD__, array(
          'ORDER BY' => array('CAST(manuscripts_lowercase_title AS UNSIGNED)', 'manuscripts_lowercase_title'),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-request');
        }

        $s = $res->fetchObject();

        return array($s->manuscripts_url, $s->manuscripts_title);
    }

    protected function formatTEIXML() {

        if (!isset($this->page_texts) || !isset($this->manuscript_title)) {
            throw new \Exception('error-request');
        }

        $xml = '';

        $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<TEI>';
        $xml .= '<text xml:id="' . $this->manuscript_title . '">';
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

    protected function outputTEIXML() {
        if (!isset($this->user_name) || !isset($this->manuscript_title) || !isset($this->TEIXML)) {
            throw new \Exception('error-request');
        }

        $response = $this->getRequest()->response();
        $filename = $this->user_name . $this->manuscript_title . '.xml';
        $response->header("Content-type: text/xml");
        $response->header('Content-Disposition: attachment; filename="' . $filename . '"');
        $response->header("Content-Length: " . strlen($this->TEIXML));
        $this->getOutput()->addHTML($this->TEIXML);
        return;
    }

}
