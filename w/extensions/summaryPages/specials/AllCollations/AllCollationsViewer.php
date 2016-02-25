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
class AllCollationsViewer extends ManuscriptDeskBaseViewer implements SummaryPageViewerInterface {

    use HTMLLetterBar,
        HTMLJavascriptLoaderGif,
        HTMLPreviousNextPageLinks;

    private $page_name;

    public function __construct($out, $page_name) {
        parent::construct();
        $this->page_name = $page_name;
    }

    /**
     * This function shows the page after a request has been processed
     */
    public function showSingleLetterOrNumberPage(
    $alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $button_name, array $page_titles, $offset, $next_offset, $max_on_page) {

        $out = $this->out;
        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet);
        $html .= $this->getHTMLPreviousNextPageLinks($out, $offset, $next_offset, $max_on_page, $button_name);
        $html .= $this->getHTMLJavascriptLoaderGif();

        $out->setPageTitle($this->msg('allcollations'));

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-trhee'>" . "<b>" . $this->msg('userpage-user') . "</b>" . "</td>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
        $html .= "</tr>";

        foreach ($title_array as $key => $array) {

            $title = isset($array['collations_main_title']) ? $array['collations_main_title'] : '';
            $url = isset($array['collations_url']) ? $array['collations_url'] : '';
            $user = isset($array['collations_user']) ? $array['collations_user'] : '';
            $date = isset($array['collations_date']) ? $array['collations_date'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-three'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" .
                htmlspecialchars($title) . "</a></td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($user) . "</td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        return $out->addHTML($html);
    }

    /**
     * This function shows the default page if no request was posted 
     */
    public function showDefaultPage($error_message, $alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet) {

        $out = $this->out;
        $out->setPageTitle($this->msg('allcollations'));

        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet);
        $html .= $this->getHTMLJavascriptLoaderGif();

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $html .= "<p>" . $this->msg('allcollations-instruction') . "</p>";

        return $out->addHTML($html);
    }

    public function showEmptyPageTitlesError(array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet, $button_name) {

        $out = $this->out;
        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $button_name);
        $out->setPageTitle($out->msg('allcollations'));

        if ($button_is_numeric) {
            $html .= "<p>" . $out->msg('allcollations-nocollections-number') . "</p>";
        }
        else {
            $html .= "<p>" . $out->msg('allcollations-nocollections') . "</p>";
        }

        return $out->addHTML($html);
    }

    protected function getPageName() {
        return $this->page_name;
    }

}
