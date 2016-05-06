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
class PageMetaTable {

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

            $html .= "<th>" . htmlspecialchars($index) . "</th>";
            $html .= "<td>" . htmlspecialchars($value) . "</td>";
            $a+=1;
        }

        $html .= "</tr>";
        $html .= "</table>";

        return $html;
    }

    /**
     * Extract options from a blob of text
     */
    public function setInputValuesFromTagContent($input) {
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

        return $this->input_values = $values;
    }

    public function setInputValuesFromArray(array $pystyl_config) {

        $removenonalpha = isset($pystyl_config['removenonalpha']) ? $pystyl_config['removenonalpha'] : '';
        $lowercase = isset($pystyl_config['lowercase']) ? $pystyl_config['lowercase'] : '';
        $tokenizer = isset($pystyl_config['tokenizer']) ? $pystyl_config['tokenizer'] : '';
        $minimumsize = isset($pystyl_config['minimumsize']) ? $pystyl_config['minimumsize'] : '';
        $maximumsize = isset($pystyl_config['maximumsize']) ? $pystyl_config['maximumsize'] : '';
        $segmentsize = isset($pystyl_config['segmentsize']) ? $pystyl_config['segmentsize'] : '';
        $stepsize = isset($pystyl_config['stepsize']) ? $pystyl_config['stepsize'] : '';
        $removepronouns = isset($pystyl_config['removepronouns']) ? $pystyl_config['removepronouns'] : '';
        $vectorspace = isset($pystyl_config['vectorspace']) ? $pystyl_config['vectorspace'] : '';
        $featuretype = isset($pystyl_config['featuretype']) ? $pystyl_config['featuretype'] : '';
        $ngramsize = isset($pystyl_config['ngramsize']) ? $pystyl_config['ngramsize'] : '';
        $mfi = isset($pystyl_config['mfi']) ? $pystyl_config['mfi'] : '';
        $minimumdf = isset($pystyl_config['minimumdf']) ? $pystyl_config['minimumdf'] : '';
        $maximumdf = isset($pystyl_config['maximumdf']) ? $pystyl_config['maximumdf'] : '';
 
        $input_values = $this->input_values;
        $input_values['Remove non-alpha:'] = $removenonalpha;
        $input_values['Lowercase:'] = $lowercase;
        $input_values['Tokenizer:'] = $tokenizer;
        $input_values['Minimum Size:'] = $minimumsize;
        $input_values['Maximum Size:'] = $maximumsize;
        $input_values['Segment Size:'] = $segmentsize;
        $input_values['Step Size:'] = $stepsize;
        $input_values['Rmove Pronouns:'] = $removepronouns;
        $input_values['Vector Space:'] = $vectorspace;
        $input_values['Feature Type:'] = $featuretype;
        $input_values['Ngram Size:'] = $ngramsize;
        $input_values['MFI:'] = $mfi;
        $input_values['Minimum DF:'] = $minimumdf;
        $input_values['Maximum DF:'] = $maximumdf;

        return $this->input_values = $input_values;
    }

}
