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
class SpecialRecentManuscriptPages extends SpecialPage {

    /**
     * Specialrecentmanuscriptpages page. Organises the most recently added manuscript pages 
     */
    public function __construct() {

        parent::__construct('RecentManuscriptPages');
    }

    /**
     * Main entry point for the page
     */
    public function execute() {
        $page_titles = $this->getData();
        $this->showDefaultPage($page_titles);
        return true;
    }

    /**
     * This function shows the page after a request has been processed
     */
    private function showDefaultPage(array $page_titles) {

        global $wgArticleUrl;
        $out = $this->getOutput();
        $article_url = $wgArticleUrl;

        $out->setPageTitle($this->msg('recentmanuscriptpages'));

        if (empty($page_titles)) {
            return $out->addWikiText($this->msg('recentmanuscriptpages-nomanuscripts'));
        }

        $html = "";
        $html .= $this->msg('recentmanuscriptpages-information');

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-four'><b>" . $this->msg('userpage-tabletitle') . "</b></td>";
        $html .= "<td class='td-four'><b>" . $this->msg('userpage-user') . "</b></td>";
        $html .= "<td class='td-four'><b>" . $this->msg('userpage-collection') . "</b></td>";
        $html .= "<td class='td-four'><b>" . $this->msg('userpage-creationdate') . "</b></td>";
        $html .= "</tr>";

        foreach ($page_titles as $array) {

            $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
            $user = isset($array['manuscripts_user']) ? $array['manuscripts_user'] : '';
            $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
            $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
            $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';

            $html .= "<tr>";
            $html .= "<td class='td-four'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" .
                htmlspecialchars($title) . "</a></td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($user) . "</td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($collection) . "</td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        return $out->addHTML($html);
    }

    public function getData() {

        global $wgNewManuscriptOptions;

        $max_on_page = $wgNewManuscriptOptions['max_recent'];

        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
          'manuscripts_user',
          'manuscripts_url',
          'manuscripts_date',
          'manuscripts_collection',
          'manuscripts_lowercase_title',
          'manuscripts_datesort'
            ), array(
          'manuscripts_lowercase_title >= ' . $dbr->addQuotes(""),
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_datesort DESC',
          'LIMIT' => $max_on_page,
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                $title_array[] = array(
                  'manuscripts_title' => $s->manuscripts_title,
                  'manuscripts_user' => $s->manuscripts_user,
                  'manuscripts_url' => $s->manuscripts_url,
                  'manuscripts_date' => $s->manuscripts_date,
                  'manuscripts_collection' => $s->manuscripts_collection,
                );
            }
        }

        return $title_array;
    }

}
