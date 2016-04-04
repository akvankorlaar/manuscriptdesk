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
class HelperScriptsViewer extends ManuscriptDeskBaseViewer {

    use HTMLJavascriptLoader;

    public function showDefaultPage($error_message = '') {

        global $wgArticleUrl;
        $out = $this->out;

        $out->setPageTitle($out->msg('helperscripts'));

        $html = '';
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $html .= "<p>" . $out->msg('helperscripts-instruction') . "</p>";

        $edit_token = $out->getUser()->getEditToken();

        $alphabetnumbers_message = $out->msg('alphabetnumbers-message');
        $delete_manuscripts_message = $out->msg('delete-manuscripts-message');

        $html .= '<form class="manuscriptdesk-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
        $html .= "<input type='submit' name='update_alphabetnumbers_posted' value='$alphabetnumbers_message'>";
        $html .= "<input type='submit' name='delete_manuscripts_posted' value='$delete_manuscripts_message'>";
        $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= '</form>';

        $html .= "</div>";

        return $out->addHTML($html);
    }

    public function showActionComplete() {
        $out = $this->out;
        $out->setPageTitle($out->msg('helperscripts'));
        $html = "<p>" . $out->msg('action-complete') . "</p>";
        $out->addHTML($html);      
    }

}
