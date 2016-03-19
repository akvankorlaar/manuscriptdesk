<?php

/**
 * This file is part of the NewManuscript extension
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
class PageMetaTableFromTags {

    private $input_values;

    public function __construct() {
        
    }

    /**
     * This function renders the metadata table 
     */
    public function renderTable() {

        $values = $this->input_values;

        if (!isset($values)) {
            return '';
        }

        $html = "";
        $html .= "<table id='page-metatable' align='center'>";
        $html .= "<tr>";

        $a = 0;
        foreach ($values as $index => $value) {

            if ($a % 2 === 0) {
                $html .= "</tr>";
                $html .= "<tr>";
            }

            $html .= "<th>" . $index . "</th>";
            $html .= "<td>" . $value . "</td>";
            $a+=1;
        }

        $html .= "</tr>";
        $html .= "</table>";

        return $html;
    }

    /**
     * Extract options from a blob of text
     */
    public function extractOptions($input) {
        //Parse all possible options
        $values = array();
        $input_array = explode("\n", $input);

        foreach ($input_array as $line) {

            if (strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $value = preg_replace('/[^A-Za-z0-9 :]/', '', $value);
            $name = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
            $values[strtolower(trim($name))] = $value;
        }

        $this->input_values = $values;

        return true;
    }

}
