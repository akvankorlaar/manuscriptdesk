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

trait HTMLUserPageMenuBar {

    /**
     * This function constructs the menu bar for the user page
     */
    protected function getHTMLUserPageMenuBar(OutputPage $out, $edit_token, array $button_ids = array()) {

        global $wgArticleUrl;

        $manuscripts_message = $out->msg('userpage-mymanuscripts');
        $collations_message = $out->msg('userpage-mycollations');
        $collections_message = $out->msg('userpage-mycollections');

        $id_manuscripts = isset($button_ids[0]) ? $button_ids[0] : 'button';
        $id_collations = isset($button_ids[1]) ? $button_ids[1] : 'button';
        $id_collections = isset($button_ids[2]) ? $button_ids[2] : 'button';

        $html = '<form class="summarypage-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
        $html .= "<input type='submit' name='view_manuscripts_posted' id='$id_manuscripts' value='$manuscripts_message'>";
        $html .= "<input type='submit' name='view_collations_posted' id='$id_collations' value='$collations_message'>";
        $html .= "<input type='submit' name='view_collections_posted' id='$id_collections' value='$collections_message'>";
        $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= '</form>';

        return $html;
    }

}
