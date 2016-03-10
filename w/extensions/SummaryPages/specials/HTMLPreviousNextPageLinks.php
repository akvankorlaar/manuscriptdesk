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
trait HTMLPreviousNextPageLinks {

    protected function getHTMLPreviousNextPageLinks(OutputPage $out, $edit_token, $offset, $next_offset, $button_name, $page_name) {
        
        global $wgNewManuscriptOptions, $wgArticleUrl;
        
        $max_on_page = $wgNewManuscriptOptions['max_on_page'];
        
        $html = "";

        if ($offset >= $max_on_page) {

            $previous_message_hover = $out->msg('singlemanuscriptpages-previoushover');
            $previous_message = $out->msg('singlemanuscriptpages-previous');

            $previous_offset = ($offset) - ($max_on_page);

            $html .='<form class="summarypage-form" id="previous-link" action="' . $wgArticleUrl . 'Special:' . $page_name . '" method="post">';
            $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
            $html .= "<input type='hidden' name='$button_name' value='$button_name'>";
            $html .= "<input type='submit' class='button-transparent' name='redirect_page_back' title='$previous_message_hover'  value='$previous_message'>";
            $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
            $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
            $html .= "</form>";
        }

        if (isset($next_offset)) {

            if (!$offset >= $max_on_page) {
                $html.='<br>';
            }

            $next_message_hover = $out->msg('singlemanuscriptpages-nexthover');
            $next_message = $out->msg('singlemanuscriptpages-next');

            $html .= '<form class="summarypage-form" id="next-link" action="' . $wgArticleUrl . 'Special:' . $page_name . '" method="post">';
            $html .= "<input type='hidden' name='offset' value = '$next_offset'>";
            $html .= "<input type='hidden' name='$button_name' value='$button_name'>";
            $html .= "<input type='submit' class='button-transparent' name = 'redirect_page_forward' title='$next_message_hover' value='$next_message'>";
            $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
            $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
            $html .= "</form>";
        }

        return $html;
    }
        
}
