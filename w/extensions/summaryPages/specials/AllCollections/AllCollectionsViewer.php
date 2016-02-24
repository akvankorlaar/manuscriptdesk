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
class AllCollectionsViewer extends ManuscriptDeskBaseViewer {

    use HTMLLetterBar, HTMLJavascriptLoaderGif;

    /**
     * This function shows the page after a request has been processed
     */
    public function showSingleLetterOrNumberPage(array $collection_titles, $next_offset, $next_page_possible, $button_name, $offset, $previous_page_possible, $max_on_page) {

        $out = $this->out;

        if ($previous_page_possible) {

            $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
            $previous_message = $this->msg('allmanuscriptpages-previous');

            $previous_offset = ($offset) - ($max_on_page);

            $html .='<form class="summarypage-form" id="previous-link" action="' . $article_url . 'Special:AllCollections" method="post">';

            $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
            $html .= "<input type='hidden' name='$button_name' value='$button_name'>";
            $html .= "<input type='submit' class='button-transparent' name='redirect_page_back' title='$previous_message_hover'  value='$previous_message'>";

            $html.= "</form>";
        }

        if ($next_page_possible) {

            if (!$previous_page_possible) {
                $html.='<br>';
            }

            $next_message_hover = $this->msg('allmanuscriptpages-nexthover');
            $next_message = $this->msg('allmanuscriptpages-next');

            $html .='<form class="summarypage-form" id="next-link" action="' . $article_url . 'Special:AllCollections" method="post">';

            $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
            $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
            $html .= "<input type='submit' class='button-transparent' name = 'redirect_page_forward' title='$next_message_hover' value='$next_message'>";

            $html.= "</form>";
        }

        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<form id='allcollections-post' action='" . $article_url . "Special:AllCollections' method='post'>";
        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-collection') . "</b>" . "</td>";
        $html .= "<td class='td-trhee'>" . "<b>" . $this->msg('userpage-user') . "</b>" . "</td>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
        $html .= "</tr>";

        foreach ($collection_titles as $single_collection_data) {

            $title = isset($single_collection_data['collections_title']) ? $single_collection_data['collections_title'] : '';
            $user = isset($single_collection_data['collections_user']) ? $single_collection_data['collections_user'] : '';
            $date = isset($single_collection_data['collections_date']) ? $single_collection_data['collections_date'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-three'>";
            $html .= "<input type='submit' class='button-transparent' name='singlecollection' value='" . htmlspecialchars($title) . "'>";
            $html .= "</td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($user) . "</td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</form>";

        //this has to be added explicitly and not in the hook because somehow mPrefixedText does not work in this case
        $out->addModuleStyles('ext.userPage');

        return $out->addHTML($html);
    }

    /**
     * This function shows single collection data
     */
    public function showSingleCollectionData($single_collection_data, $alphabet_numbers = array(), $selected_collection) {

        global $wgArticleUrl;

        $out = $this->getOutput();
        $article_url = $wgArticleUrl;
        $selected_collection;
        list($meta_data, $pages_within_collection) = $single_collection_data;

        $out->setPageTitle($this->msg('allcollections'));

        $html .= $this->getHTMLLetterBar($data);
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";

        $html .= "<h2 style='text-align: center;'>" . $this->msg('userpage-collection') . ": " . $selected_collection . "</h2>";
        $html .= "<br>";
        $html .= "<h3>" . $this->msg('userpage-metadata') . "</h3>";

        $collection_meta_table = new collectionMetaTable();

        $html .= $collection_meta_table->renderTable($meta_data);

        $html .= "<h3>Pages</h3>";
        $html .= $this->msg('userpage-contains') . " " . count($pages_within_collection) . " " . $this->msg('userpage-contains2');
        $html .= "<br>";

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
        $html .= "</tr>";

        foreach ($pages_within_collection as $key => $array) {

            $manuscripts_url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
            $manuscripts_title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
            $manuscripts_date = isset($array['manuscripts_date']) ? $array['manuscripts_date'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-long'><a href='" . $article_url . htmlspecialchars($manuscripts_url) . "' title='" . htmlspecialchars($manuscripts_url) . "'>" .
                htmlspecialchars($manuscripts_title) . "</a></td>";
            $html .= "<td>" . htmlspecialchars($manuscripts_date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";

        $html .= "</div>";

        return $out->addHTML($html);
    }

    /**
     * This function shows the default page if no request was posted 
     */
    public function showDefaultPage(array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet) {

        $out = $this->out;

        $out->setPageTitle($this->msg('allcollections'));
        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet);
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<p>" . $this->msg('allcollections-instruction') . "</p>";

        return $out->addHTML($html);
    }

    public function showEmptyPageTitlesError(array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet, $button_name) {

        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $button_name);

        if ($button_is_numeric) {
            $html .= "<p>" . $this->msg('allcollections-nocollections-number') . "</p>";
        }
        else {
            $html .= "<p>" . $this->msg('allcollections-nocollections') . "</p>";
        }

        return $out->addHTML($html);
    }

}
