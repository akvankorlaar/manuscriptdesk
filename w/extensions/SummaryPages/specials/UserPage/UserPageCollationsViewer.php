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

class UserPageCollationsViewer implements UserPageViewerInterface {

    use HTMLUserPageMenuBar,
        HTMLJavascriptLoaderDots, HTMLPreviousNextPageLinks;

    private $out;
    private $user_name;

    public function __construct(OutputPage $out, $user_name) {
        $this->out = $out;
        $this->user_name = $user_name; 
    }

    public function showPage($button_name, $page_titles, $offset, $next_offset) {

        $out = $this->out;
        global $wgArticleUrl;
        $user_name = $this->user_name;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button-active', 'button'));
        $html .= $this->getHTMLJavascriptLoaderDots();
        
        $html .= "<div class='javascripthide'>";
        
        $html .= $this->getHTMLPreviousNextPageLinks($out, $edit_token, $offset, $next_offset, $button_name, 'UserPage');

        $created_message = $out->msg('userpage-created');
        $html .= "<br>";

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-three'>" . "<b>" . $out->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-three'><b>" . $out->msg('userpage-signature') . "</b></td>";
        $html .= "<td class='td-three'><b>" . $out->msg('userpage-creationdate') . "</b></td>";
        $html .= "</tr>";

        foreach ($page_titles as $single_page_data) {

            $partial_url = isset($single_page_data['collations_url']) ? $single_page_data['collations_url'] : '';
            $date = isset($single_page_data['collations_date']) ? $single_page_data['collations_date'] : '';
            $title = isset($single_page_data['collations_main_title']) ? $single_page_data['collations_main_title'] : '';
            $signature = isset($single_page_data['collations_signature']) ? $single_page_data['collations_signature'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-long'><a href='" . $wgArticleUrl . htmlspecialchars($partial_url) . "' title='" . htmlspecialchars($title) . "'>" .
                htmlspecialchars($title) . "</a></td>";
            $html .= "<td>" . $this->getChangeSignatureCollationPageForm($partial_url, $signature, $button_name, $offset) . "</td>";
            $html .= "<td>" . htmlspecialchars($date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "<input type='hidden' name='view_collations_posted' value='view_collations_posted'>";
        $html .= "</div>";

        return $out->addHTML($html);
    }
    
    private function getChangeSignatureCollationPageForm($partial_url, $signature, $button_name, $offset){

        global $wgArticleUrl;
        $edit_token = $this->out->getUser()->getEditToken();

        if ($signature === 'private') {
            $new_signature = 'public';
        }
        else {
            $new_signature = 'private';
        }

        $html = "";
        $html .= '<form class="manuscriptpage-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
        $html .= "<input class='button-transparent' type='submit' name='editlink' value='$signature'>";
        $html .= "<input type='hidden' name='partial_url' value='$partial_url'>";
        $html .= "<input type='hidden' name='change_signature_collation_posted' value = '$new_signature'>";
          $html .= "<input type='hidden' name='button_name' value = '$button_name'>";
        $html .= "<input type='hidden' name='offset' value = '$offset'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        return $html;
    }

    public function showEmptyPageTitlesError($button_name) {

        global $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);
        
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button', 'button-active', 'button'));
        $html .= $this->getHTMLJavascriptLoaderDots();
       
        $html .= "<div class='javascripthide'>";
        
        $html .= "<p>" . $out->msg('userpage-nocollations') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $wgArticleUrl . "Special:Collate'>" . $out->msg('userpage-newcollation') . "</a></p>";
        
        $html .= "</div>";

        return $out->addHTML($html);
    }
    
}
