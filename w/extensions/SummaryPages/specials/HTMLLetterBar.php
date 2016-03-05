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

trait HTMLLetterBar {
    
    protected function getHTMLLetterBar(array $alphabet_numbers, array $uppercase_alphabet, array $lowercase_alphabet, $button_name = null) {

        $a = 0;

        foreach ($uppercase_alphabet as $key => $value) {

            if ($a === 0) {
                $html .= "<tr>";
            }

            if ($a === (count($uppercase_alphabet) / 2)) {
                $html .= "</tr>";
                $html .= "<tr>";
            }

            $name = $lowercase_alphabet[$key];

            if (isset($alphabet_numbers[$key]) && $alphabet_numbers[$key] > 0) {
                $alphabet_number = $alphabet_numbers[$key];
            }
            else {
                $alphabet_number = '';
            }

            if (isset($button_name) && $button_name === $name) {
                $html .= "<td>";
                $html .= "<div class='letter-div-active' style='display:inline-block;'>";
                $html .= "<input type='submit' name='$name' class='letter-button-active' value='$value'>";
                $html .= "<small>$alphabet_number</small>";
                $html .= "</div>";
                $html .= "</td>";
            }
            else {
                $html .= "<td>";
                $html .= "<div class='letter-div-initial' style='display:inline-block;'>";
                $html .= "<input type='submit' name='$name' class='letter-button-initial' value='$value'>";
                $html .= "<small>$alphabet_number</small>";
                $html .= "</div>";
                $html .= "</td>";
            }

            $a+=1;
        }

        $html .= "</tr>";
        $html .= "</table>";
        $html .= '</form><br>';

        return $html;
    }
    
    
}