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
 */
class AllStylometricAnalysisViewer extends ManuscriptDeskBaseViewer implements SummaryPageViewerInterface {

    use HTMLLetterBar,
        HTMLJavascriptLoaderDots,
        HTMLPreviousNextPageLinks;

    private $page_name;

    public function setPageName($page_name) {

        if (isset($this->page_name)) {
            return;
        }

        return $this->page_name = $page_name;
    }

    /**
     * This function shows the page after a request has been processed
     */
    public function showSingleLetterOrNumberPage(
    array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet, $button_name, array $page_titles, $offset, $next_offset) {

        global $wgArticleUrl;
        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();
        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $this->page_name, $button_name);
        $html .= $this->getHTMLJavascriptLoaderDots();

        $html .= "<div class='javascripthide'>";

        $html .= $this->getHTMLPreviousNextPageLinks($out, $edit_token, $offset, $next_offset, $this->page_name, $button_name);

        $out->setPageTitle($out->msg('allstylometricanalysis'));

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-four'>" . "<b>" . $out->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-four'>" . "<b>" . $out->msg('userpage-user') . "</b>" . "</td>";
        $html .= "<td class='td-four'>" . "<b>" . $out->msg('userpage-signature') . "</b>" . "</td>";
        $html .= "<td class='td-four'>" . "<b>" . $out->msg('userpage-creationdate') . "</b>" . "</td>";
        $html .= "</tr>";

        foreach ($page_titles as $single_page_data) {

            $title = isset($single_page_data['stylometricanalysis_main_title']) ? $single_page_data['stylometricanalysis_main_title'] : '';
            $url = isset($single_page_data['stylometricanalysis_new_page_url']) ? $single_page_data['stylometricanalysis_new_page_url'] : '';
            $user = isset($single_page_data['stylometricanalysis_user']) ? $single_page_data['stylometricanalysis_user'] : '';
            $date = isset($single_page_data['stylometricanalysis_date']) ? $single_page_data['stylometricanalysis_date'] : '';
            $signature = isset($single_page_data['stylometricanalysis_signature']) ? $single_page_data['stylometricanalysis_signature'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-four'><a href='" . $wgArticleUrl . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" .
                htmlspecialchars($title) . "</a></td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($user) . "</td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($signature) . "</td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    /**
     * This function shows the default page if no request was posted 
     */
    public function showDefaultPage($error_message, array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet) {

        $out = $this->out;
        $out->setPageTitle($out->msg('allstylometricanalysis'));

        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $this->page_name);
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $html .= "<p>" . $out->msg('allstylometricanalysis-instruction') . "</p>";

        $html .= "</div>";

        return $out->addHTML($html);
    }

    public function showEmptyPageTitlesError(array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet, $button_name) {

        $out = $this->out;
        $out->setPageTitle($out->msg('allstylometricanalysis'));

        $html = '';
        $html .= $this->getHTMLLetterBar($alphabet_numbers, $uppercase_alphabet, $lowercase_alphabet, $this->page_name, $button_name);
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";

        if (preg_match('/^[0-9.]*$/', $button_name)) {
            $html .= "<p>" . $out->msg('allstylometricanalysis-nostylometricanalysis-number') . "</p>";
        }
        else {
            $html .= "<p>" . $out->msg('allstylometricanalysis-nostylometricanalysis') . "</p>";
        }

        $html .= "</div>";

        return $out->addHTML($html);
    }

}
