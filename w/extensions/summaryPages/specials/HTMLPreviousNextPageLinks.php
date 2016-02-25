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

    protected function getHTMLPreviousNextPageLinks(OutputPage $out, $offset, $next_offset, $max_on_page, $button_name, $page_name) {

        $html = "";

        if ($offset >= $max_on_page) {

            $previous_message_hover = $out->msg('allmanuscriptpages-previoushover');
            $previous_message = $out->msg('allmanuscriptpages-previous');

            $previous_offset = ($offset) - ($max_on_page);

            $html .='<form class="summarypage-form" id="previous-link" action="' . $article_url . 'Special:' . $this->getPageName() . '" method="post">';

            $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
            $html .= "<input type='hidden' name='$button_name' value='$button_name'>";
            $html .= "<input type='submit' class='button-transparent' name='redirect_page_back' title='$previous_message_hover'  value='$previous_message'>";

            $html.= "</form>";
        }

        if (isset($next_offset)) {

            if (!$offset >= $max_on_page) {
                $html.='<br>';
            }

            $next_message_hover = $out->msg('allmanuscriptpages-nexthover');
            $next_message = $out->msg('allmanuscriptpages-next');

            $html .='<form class="summarypage-form" id="next-link" action="' . $article_url . 'Special:' . $this->getPageName() . '" method="post">';

            $html .= "<input type='hidden' name='offset' value = '$next_offset'>";
            $html .= "<input type='hidden' name='$button_name' value='$button_name'>";
            $html .= "<input type='submit' class='button-transparent' name = 'redirect_page_forward' title='$next_message_hover' value='$next_message'>";

            $html.= "</form>";
        }

        return $html;
    }
    
    abstract protected function getPageName();
    
}
