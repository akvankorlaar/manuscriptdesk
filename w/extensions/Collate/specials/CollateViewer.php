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

    public function __construct($out) {
        $this->out = $out;       
    }

    /**
     * This function constructs the HTML for the default page
     */
    public function showForm1(array $manuscript_data, array $collection_data) {
        
        global $wgArticleUrl;
        $out = $this->out; 
        $manuscript_urls = isset($manuscript_data['manuscript_urls']) ? $manuscript_data['manuscript_urls'] : array();
        $manuscript_titles = isset($manuscript_data['manuscript_titles']) ? $manuscript_data['manuscript_titles'] : array();
        $article_url = $wgArticleUrl;

        $out->setPageTitle($this->msg('collate-welcome'));

        $about_message = $this->msg('collate-about');
        $version_message = $this->msg('collate-version');
        $software_message = $this->msg('collate-software');
        $lastedit_message = $this->msg('collate-lastedit');

        $html = "<table id='begincollate-infobox'>";
        $html .= "<tr><th>$about_message</th></tr>";
        $html .= "<tr><td>$version_message</td></tr>";
        $html .= "<tr><td>$software_message <a href= 'http://collatex.net' target='_blank'> Collatex Tools 1.7.0</a>.</td></tr>";
        $html .= "<tr><td id='begincollate-infobox-lasttd'><small>$lastedit_message</small></td></tr>";
        $html .= "</table>";

        $html .= "<p>" . $this->msg('collate-instruction1') . "</p>";

        if (!empty($collection_data)) {
            $html .= "<p>" . $this->msg('collate-instruction2') . "</p>";
        }

        $html .= "<div id='javascript-error'></div>";

        if ($this->error_message) {
            $error_message = $this->error_message;
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $manuscript_message = $this->msg('collate-manuscriptpages');

        $html .= "<form id='begincollate-form' action='" . $article_url . "Special:BeginCollate' method='post'>";
        $html .= "<h3>$manuscript_message</h3>";
        $html .= "<table class='begincollate-table'>";

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
            $html .="<input type='checkbox' class='begincollate-checkbox' name='manuscripts_urls$index' value='" . htmlspecialchars($url) . "'>" . htmlspecialchars($title_name);
            $html .= "</td>";
            $a+=1;
        }

        $html .= "</tr>";
        $html .= "</table>";

        if (!empty($collection_data)) {

            $collection_message = $this->msg('collate-collections');
            $html .= "<h3>$collection_message</h3>";
            $html .= "<table class='begincollate-table'>";

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
                $collection_text = $this->msg('collate-contains') . $manuscript_pages_within_collection . '.';

                //add a checkbox for the collection
                $html .="<td>";
                $html .="<input type='checkbox' class='begincollate-checkbox-col' name='collection_urls$a' value='$json_manuscript_collection_urls'>" . htmlspecialchars($collection_name);
                $html .="<input type='hidden' name='collection_titles$a' value='" . htmlspecialchars($collection_name) . "'>";
                $html .= "<br>";
                $html .= "<span class='begincollate-span'>" . $collection_text . "</span>";
                $html .="</td>";
                $a = ++$a;
            }

            $html .= "</tr>";
            $html .= "</table>";
        }

        $html .= "<br><br>";

        $submit_hover_message = $this->msg('collate-hover');
        $submit_message = $this->msg('collate-submit');
        $edit_token = $out->getUser()->getEditToken();

        $html .= "<input type='submit' disabled id='begincollate-submitbutton' title = $submit_hover_message value=$submit_message>";
        $html .= "<input type='hidden' name='form1Posted' value='form1Posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .="</form>";
        $html .= "<br>";
        $html .= $this->AddBeginCollateLoader();

        $out->addHTML($html);
    }

    /**
     * Generate HTML for the collate table, based on collatex output
     */
    public function showCollateNamespacePage(array $data) {

        $user_name = isset($data['user_name']) ? $data['user_name'] : '';
        $date = isset($data['date']) ? $data['date'] : '';
        $titles_array = isset($data['titles_array']) ? $data['titles_array'] : '';
        $collatex_output = isset($data['collatex_output']) ? $data['collatex_output'] : '';

        $html = "";

        if ($user_name && $date) {
            $html .= "This page has been created by: " . htmlspecialchars($user_name) . "<br> Date: " . htmlspecialchars($date) . "<br> ";
        }

        $collatex_output = preg_replace('/[<>]/', '', $collatex_output);

        $html .= "
       <script> var at = $collatex_output;</script>
       <script type='text/javascript' src='https://yui-s.yahooapis.com/3.18.1/build/yui/yui-min.js'></script>
       <script src='/w/extensions/collate/specials/javascriptcss/jquery.min.js'></script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/collatex.js'></script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/collatexTwo.js'></script>
       <link rel='stylesheet' type='text/css' href='/w/extensions/collate/specials/javascriptcss/collatex.css'>";

        $html .="
      <table class='alignment'>";

        foreach ($titles_array as $key => $title) {
            $html .=
                "<tr><th>" . htmlspecialchars($title) . "</th></tr>";
        }

        $html .= "         
    </table>
    <div id='body'>
      <div id='result'>
      </div>
    </div>";

        return $html;
    }

    public function showFewManuscriptsError() {

        global $wgArticleUrl;

        $article_url = $wgArticleUrl;

        $html = "";
        $html .= $this->msg('collate-fewuploads');
        $html .= "<p><a class='begincollate-transparent' href='" . $article_url . "Special:NewManuscript'>Create a new manuscript page</a></p>";

        return $this->out->addHTML($html);
    }

}
