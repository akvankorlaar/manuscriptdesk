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
 * 
 */
abstract class UserPageBaseViewer extends ManuscriptDeskBaseViewer {
    
    protected $user_name; 

    /**
     * Get HTML form to download the single manuscript page in TEI format
     * 
     * @global type $wgArticleUrl
     * @param type $manuscripts_title
     * @return string HTML
     */
    protected function getExportManuscriptTEIForm($manuscripts_title) {
        global $wgArticleUrl;
        $user_name = $this->user_name;
        $out = $this->out;
        $collection_tei_export = $wgArticleUrl . "Special:ManuscriptTEIExport?username=" . $user_name . "&manuscript=" . $manuscripts_title;
        $html = '';
        $html .= "<form class='manuscriptpage-form' action='" . $collection_tei_export . "' method='post'>";
        $html .= "<input type='submit' class='button-transparent' value='" . $out->msg('teiexport') . "'>";
        $html .= "</form>";
        return $html;
    }

}
