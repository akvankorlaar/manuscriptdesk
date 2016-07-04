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
class UserPageManuscriptsViewer extends UserPageBaseViewer implements UserPageViewerInterface {

    use HTMLUserPageMenuBar,
        HTMLJavascriptLoaderDots,
        HTMLPreviousNextPageLinks;

    public function setUserName($user_name) {

        if (isset($this->user_name)) {
            return;
        }

        return $this->user_name = $user_name;
    }

    public function showPage($button_name, $page_data, $offset, $next_offset) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button-active', 'button', 'button'));
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";
        $html .= $this->getHTMLPreviousNextPageLinks($out, $edit_token, $offset, $next_offset, $button_name, 'UserPage');

        $created_message = $out->msg('userpage-created');
        $html .= "<br>";

        $html .= "<p>" . $out->msg('userpage-manuscriptinstr') . "</p>";

        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-four'><b>" . $out->msg('userpage-tabletitle') . "</b></td>";
        $html .= "<td class='td-four'><b>" . $out->msg('userpage-creationdate') . "</b></td>";
        $html .= "<td class='td-four'><b>" . $out->msg('userpage-signature') . "</b></td>";
        $html .= "<td class='td-four'></td>";
        $html .= "</tr>";

        foreach ($page_data as $single_page_data) {

            $title = isset($single_page_data['manuscripts_title']) ? $single_page_data['manuscripts_title'] : '';
            $partial_url = isset($single_page_data['manuscripts_url']) ? $single_page_data['manuscripts_url'] : '';
            $date = $single_page_data['manuscripts_date'] !== '' ? $single_page_data['manuscripts_date'] : 'unknown';
            $signature = isset($single_page_data['manuscripts_signature']) ? $single_page_data['manuscripts_signature'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-four'><a href='" . $article_url . htmlspecialchars($partial_url) . "' title='" . htmlspecialchars($title) . "'>" .
                htmlspecialchars($title) . "</a></td>";
            $html .= "<td class='td-four'>" . htmlspecialchars($date) . "</td>";
            $html .= "<td class='td-four'>" . $this->getChangeSignatureForm($partial_url, $signature, $button_name, $offset) . "</td>";
            $html .= "<td class='td-four'>" . $this->getExportManuscriptTEIForm($title) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    private function getChangeSignatureForm($partial_url, $signature, $button_name, $offset) {

        if ($signature === 'private') {
            $new_signature = 'public';
        }
        else {
            $new_signature = 'private';
        }

        global $wgArticleUrl;

        $edit_token = $this->out->getUser()->getEditToken();

        $html = "";
        $html .= '<form class="manuscriptpage-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
        $html .= "<input class='button-transparent' type='submit' name='editlink' value='$signature'>";
        $html .= "<input type='hidden' name='partial_url' value='$partial_url'>";
        $html .= "<input type='hidden' name='change_signature_manuscript_posted' value = '$new_signature'>";
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
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token, array('button-active', 'button', 'button'));
        $html .= $this->getHTMLJavascriptLoaderDots();
        $html .= "<div class='javascripthide'>";
        $html .= "<p>" . $out->msg('userpage-nomanuscripts') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $wgArticleUrl . "Special:NewManuscript'>" . $out->msg('userpage-newmanuscriptpage') . "</a></p>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

}
