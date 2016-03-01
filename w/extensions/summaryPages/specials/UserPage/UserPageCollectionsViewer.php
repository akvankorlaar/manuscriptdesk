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

class UserPageCollectionsViewer {

    use HTMLUserPageMenuBar,
        HTMLJavascriptLoaderGif, HTMLPreviousNextPageLinks;

    private $out;
    private $user_name;

    public function __construct(OutputPage $out, $user_name) {
        $this->out = $out;
        $this->user_name = $user_name; 
    }

    public function showPage($button_name, $page_titles, $offset, $next_offset) {

        $out = $this->out;
        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $user_name = $this->user_name;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderGif();
        $html .= $this->getHTMLPreviousNextPageLinks($out, $button_name, $offset, $next_offset, 'UserPage');

        $created_message = $this->msg('userpage-created');
        $html .= "<br>";
              
            $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $article_url . "Special:UserPage' method='post'>";
            $html .= "<table id='userpage-table' style='width: 100%;'>";
            $html .= "<tr>";
            $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
            $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
            $html .= "</tr>";

            foreach ($page_titles as $key => $array) {

                $collections_title = isset($array['collections_title']) ? $array['collections_title'] : '';
                $collections_date = isset($array['collections_date']) ? $array['collections_date'] : '';

                $html .= "<tr>";
                $html .= "<td class='td-long'><input type='submit' class='userpage-collectionlist' name='singlecollection' value='" . htmlspecialchars($collections_title) . "'></td>";
                $html .= "<td>" . htmlspecialchars($collections_date) . "</td>";
                $html .= "</tr>";
            }

            $html .= "</table>";
            $html .= "<input type='hidden' name='single_collection_posted' value='single_collection_posted'>";
            $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
            $html .= "</form>";

        return $out->addHTML($html);
    }

    protected function showEmptyPageTitlesError($button_name) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= "<p>" . $this->msg('userpage-nocollections') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $this->msg('userpage-newcollection') . "</a></p>";
        $html .= $this->getHTMLJavascriptLoaderGif();

        return $out->addHTML($html);
    }
    
}


