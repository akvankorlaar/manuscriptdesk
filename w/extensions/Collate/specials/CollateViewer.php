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
class CollateViewer extends ManuscriptDeskBaseViewer {

    use HTMLJavascriptLoader;

    /**
     * This function constructs the HTML for the default page
     */
    public function showDefaultPage($error_message, array $manuscript_data, array $collection_data) {

        global $wgArticleUrl;
        $out = $this->out;
        $manuscript_urls = isset($manuscript_data['manuscript_urls']) ? $manuscript_data['manuscript_urls'] : array();
        $manuscript_titles = isset($manuscript_data['manuscript_titles']) ? $manuscript_data['manuscript_titles'] : array();

        $out->setPageTitle($out->msg('collate-welcome'));

        $about_message = $out->msg('collate-about');
        $version_message = $out->msg('collate-version');
        $software_message = $out->msg('collate-software');
        $lastedit_message = $out->msg('collate-lastedit');

        $html = "";
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class ='javascripthide'>";

        $html .= "<table id='collate-infobox'>";
        $html .= "<tr><th>$about_message</th></tr>";
        $html .= "<tr><td>$version_message</td></tr>";
        $html .= "<tr><td>$software_message <a href= 'http://collatex.net' target='_blank'> Collatex Tools 1.7.0</a>.</td></tr>";
        $html .= "<tr><td id='collate-infobox-lasttd'><small>$lastedit_message</small></td></tr>";
        $html .= "</table>";

        $html .= "<p>" . $out->msg('collate-instruction1') . "</p>";

        if (!empty($collection_data)) {
            $html .= "<p>" . $out->msg('collate-instruction2') . "</p>";
        }

        $html .= "<div class='javascripterror'></div>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class='error'>$error_message</div>";
        }

        $manuscript_message = $out->msg('collate-manuscriptpages');

        $html .= "<form class='manuscriptdesk-form' action='" . $wgArticleUrl . "Special:Collate' method='post'>";
        $html .= "<h3>$manuscript_message</h3>";
        $html .= "<table class='collate-table'>";

        //display a checkbox for each manuscript uploaded by this user
        $a = 0;
        $html .= "<tr>";
        foreach ($manuscript_urls as $index => $url) {

            if (($a % 4) === 0) {
                $html .= "</tr>";
                $html .= "<tr>";
            }

            //get corresponding title
            $title_name = $manuscript_titles[$index];

            $html .= "<td>";
            $html .= "<input type='checkbox' class='collate-checkbox' name='manuscript_urls$a' value='" . htmlspecialchars($url) . "'>" . htmlspecialchars($title_name);
            $html .= "<input type='hidden' name='manuscript_titles$a' value='" . htmlspecialchars($title_name) . "'>";
            $html .= "</td>";
            $a+=1;
        }

        $html .= "</tr>";
        $html .= "</table>";

        if (!empty($collection_data)) {

            $collection_message = $out->msg('collate-collections');
            $html .= "<h3>$collection_message</h3>";
            $html .= "<table class='collate-table'>";

            $a = 0;
            $html .= "<tr>";
            foreach ($collection_data as $collection_name => $single_collection_data) {

                if (($a % 4) === 0) {
                    $html .= "</tr>";
                    $html .= "<tr>";
                }

                $manuscript_collection_urls = $this->HTMLSpecialCharachtersArray($single_collection_data['manuscripts_url']);

                //encode the array into json to be able to place it in the checkbox value
                $json_manuscript_collection_urls = json_encode($manuscript_collection_urls);
                $manuscript_pages_within_collection = htmlspecialchars(implode(', ', $single_collection_data['manuscripts_title']));
                $collection_text = $out->msg('collate-contains') . $manuscript_pages_within_collection . '.';

                //add a checkbox for the collection
                $html .= "<td>";
                $html .= "<input type='checkbox' class='collate-checkbox-col' name='collection_urls$a' value='$json_manuscript_collection_urls'>" . htmlspecialchars($collection_name);
                $html .= "<input type='hidden' name='collection_titles$a' value='" . htmlspecialchars($collection_name) . "'>";
                $html .= "<br>";
                $html .= "<span class='collate-span'>" . $collection_text . "</span>";
                $html .= "</td>";
                $a = ++$a;
            }

            $html .= "</tr>";
            $html .= "</table>";
        }

        $html .= "<br><br>";

        $submit_hover_message = $out->msg('collate-hover');
        $submit_message = $out->msg('collate-submit');
        $edit_token = $out->getUser()->getEditToken();

        $html .= "<input type='submit' id='collate-submitbutton' title = $submit_hover_message value=$submit_message>";
        $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        $html .= "<br>";
        $html .= "</div>";

        $out->addHTML($html);
    }

    /**
     * This function constructs the HTML collation table, and buttons
     */
    public function showCollatexOutput(array $page_titles, $collatex_output, $time) {

        global $wgArticleUrl;

        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();

        $redirect_hover_message = $out->msg('collate-redirecthover');
        $redirect_message = $out->msg('collate-redirect');

        $save_hover_message = $out->msg('collate-savehover');
        $save_message = $out->msg('collate-save');

        $html = '';
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";
        $html .= "<div id = 'collate-buttons'>";
        $html .= "<form class='manuscriptdesk-form-two' action='" . $wgArticleUrl . "Special:Collate' method='post'>";
        $html .= "<input type='submit' class='manuscriptdesk-submitbutton' name='redirect_posted' title='$redirect_hover_message'  value='$redirect_message'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "<form class='manuscriptdesk-form-two' action='" . $wgArticleUrl . "Special:Collate' method='post'>";
        $html .= "<input type='submit' class='manuscriptdesk-submitbutton' name='save_page_posted' title='$save_hover_message' value='$save_message'>";
        $html .= "<input type='hidden' name='time' value='$time'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        $html .= "</div>";

        $html .= "<p>" . $out->msg('collate-success') . "</p>" . "<p>" . $out->msg('collate-tableread') . " " . $out->msg('collate-savetable') . "</p>";

        $html .= $this->getHTMLforCollatexTable($page_titles, $collatex_output);

        $html .= "</div>";

        return $out->addHTML($html);
    }

    public function showCollateNamespacePage(array $collate_data) {

        $out = $this->out;
        $user_name = isset($collate_data['user_name']) ? $collate_data['user_name'] : '';
        $date = isset($collate_data['date']) ? $collate_data['date'] : '';
        $page_titles = isset($collate_data['titles_array']) ? $collate_data['titles_array'] : '';
        $collatex_output = isset($collate_data['collatex_output']) ? $collate_data['collatex_output'] : '';

        $html = "";

        if (!empty($user_name) && !empty($date)) {
            $html .= "This page has been created by: " . htmlspecialchars($user_name) . "<br> Date: " . htmlspecialchars($date) . "<br> ";
        }

        $html .= $this->getHTMLforCollatexTable($page_titles, $collatex_output);

        return $out->addHTML($html);
    }

    private function getHTMLforCollatexTable(array $page_titles, $collatex_output) {

        global $wgScriptPath;

        $collatex_output = preg_replace('/[<>]/', '', $collatex_output);

        $html = '';

        $html .= "
       <script> var at = $collatex_output;</script>
       <script type='text/javascript' src='https://yui-s.yahooapis.com/3.18.1/build/yui/yui-min.js'></script>
       <script src='" . $wgScriptPath . "/extensions/Collate/specials/javascriptcss/jquery.min.js'></script>
       <script type='text/javascript' src='" . $wgScriptPath . "/extensions/Collate/specials/javascriptcss/collatex.js'></script>
       <script type='text/javascript' src='" . $wgScriptPath . "/extensions/Collate/specials/javascriptcss/collatexTwo.js'></script>
       <link rel='stylesheet' type='text/css' href='" . $wgScriptPath . "/extensions/Collate/specials/javascriptcss/collatex.css'>";

        $html .="
      <table class='alignment'>";

        foreach ($page_titles as $title) {
            $html .= "<tr><th>" . htmlspecialchars($title) . "</th></tr>";
        }

        $html .= "         
    </table>
    <div id='body'>
      <div id='result'>
      </div>
    </div>";

        return $html;
    }

}
