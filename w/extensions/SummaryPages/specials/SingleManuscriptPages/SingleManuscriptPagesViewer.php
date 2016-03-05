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
class SingleManuscriptPagesViewer extends ManuscriptDeskBaseViewer implements SummaryPageViewerInterface {

    use HTMLLetterBar,
        HTMLJavascriptLoaderDots,
        HTMLPreviousNextPageLinks;

    private $page_name;

    public function __construct($out, $page_name) {
        parent::construct();
        $this->page_name = $page_name;
    }

    public function showSingleLetterOrNumberPage(
    $alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $button_name, array $page_titles, $offset, $next_offset) {

        $out = $this->out;
        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $this->page_name);
        $html .= $this->getHTMLJavascriptLoaderDots();
        
        $html .= "<div class='javascripthide'>";
        
        $html .= $this->getHTMLPreviousNextPageLinks($out, $offset, $next_offset, $button_name);     

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-user') . "</b>" . "</td>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
        $html .= "</tr>";

        foreach ($title_array as $key => $array) {

            $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
            $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
            $user = isset($array['manuscripts_user']) ? $array['manuscripts_user'] : '';
            $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';

            $html .= "<tr>";
            $html .= "<td class='td-three'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" .
                htmlspecialchars($title) . "</a></td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($user) . "</td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    /**
     * This function shows the default page if no request was posted 
     */
    protected function showDefaultPage($error_message, $alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet) {

        $out = $this->out;
        $out->setPageTitle($this->msg('singlemanuscriptpages'));

        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $this->page_name);
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $html .= "<p>" . $this->msg('singlemanuscriptpages-instruction') . "</p>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    public function showEmptyPageTitlesError() {

        $out = $this->out;
        $out->setPageTitle($out->msg('allcollations'));

        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $this->page_name, $button_name);
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";
        
        if (preg_match('/^[0-9.]*$/', $button_name)) {
            $html .= "<p>" . $out->msg('allcollations-nocollections-number') . "</p>";
        }
        else {
            $html .= "<p>" . $out->msg('allcollations-nocollections') . "</p>";
        }
        
        $html .= "</div>";

        return $out->addHTML($html);
    }

}
