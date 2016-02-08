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
class StylometricAnalysisNamespacePage extends ManuscriptDeskBaseViewer {

    private $out;

    public function __construct(OutputPage $out) {
        $this->out = $out;
    }

    public function renderPage(array $data) {

        $out = $this->out;

        $data = $this->HTMLSpecialCharachtersArray($data);

        $user_name = isset($data['user']) ? $data['user'] : '';
        $time = isset($data['time']) ? $data['time'] : '';
        $full_outputpath1 = isset($data['full_outputpath1']) ? $data['full_outputpath1'] : '';
        $full_outputpath2 = isset($data['full_outputpath2']) ? $data['full_outputpath2'] : '';
        $full_linkpath1 = isset($data['full_linkpath1']) ? $data['full_linkpath1'] : '';
        $full_linkpath2 = isset($data['full_linkpath2']) ? $data['full_linkpath2'] : '';
        $config_array = isset($data['config_array']) ? $data['config_array'] : '';
        $collection_name_array = isset($data['collection_name_array']) ? $data['collection_name_array'] : '';
        $date = isset($data['date']) ? $data['date'] : '';

        $removenonalpha = isset($config_array['removenonalpha']) ? $config_array['removenonalpha'] : '';
        $lowercase = isset($config_array['lowercase']) ? $config_array['lowercase'] : '';
        $tokenizer = isset($config_array['tokenizer']) ? $config_array['tokenizer'] : '';
        $minimumsize = isset($config_array['minimumsize']) ? $config_array['minimumsize'] : '';
        $maximumsize = isset($config_array['maximumsize']) ? $config_array['maximumsize'] : '';
        $segmentsize = isset($config_array['segmentsize']) ? $config_array['segmentsize'] : '';
        $stepsize = isset($config_array['stepsize']) ? $config_array['stepsize'] : '';
        $removepronouns = isset($config_array['removepronouns']) ? $config_array['removepronouns'] : '';
        $vectorspace = isset($config_array['vectorspace']) ? $config_array['vectorspace'] : '';
        $featuretype = isset($config_array['featuretype']) ? $config_array['featuretype'] : '';
        $ngramsize = isset($config_array['ngramsize']) ? $config_array['ngramsize'] : '';
        $mfi = isset($config_array['mfi']) ? $config_array['mfi'] : '';
        $minimumdf = isset($config_array['minimumdf']) ? $config_array['minimumdf'] : '';
        $maximumdf = isset($config_array['maximumdf']) ? $config_array['maximumdf'] : '';
        $visualization1 = isset($config_array['visualization1']) ? $config_array['visualization1'] : '';
        $visualization2 = isset($config_array['visualization2']) ? $config_array['visualization2'] : '';

        $html = "";

        if (!empty($user_name) && !empty($date)) {
            $html .= "This page has been created by: " . $user_name . "<br> Date: " . $date . "<br> ";
        }
        
        $imploded_collection_name_array = implode(', ', $collection_name_array);

        $html .= "<div id='visualization-wrap' style='display:block;'>";

        $html .= "<div id='visualization-wrap1'>";
        $html .= $out->msg('stylometricanalysis-collectionsused') . $imploded_collection_name_array;
        $html .= "<h2>" . ucfirst($visualization1) . "</h2>";
        $html .= "<img src='" . $full_linkpath1 . "' alt='Visualization1' height='650' width='650'>";
        $html .= "</div>";

        $html .= "<div id='visualization-wrap2'>";
        $html .= "<h2>" . ucfirst($visualization2) . "</h2>";
        $html .= "<img src='" . $full_linkpath2 . "' alt='Visualization2' height='650' width='650'>";
        $html .= "</div>";

        $html .= "</div>";

        $html .= "<div id='analysisconfiguration'>";
        $html .= "<h2>" . $out->msg('stylometricanalysis-analysisconfiguration') . "</h2><br>";
        $html .= "Remove non-alpha: " . $removenonalpha . "<br>";
        $html .= "Lowercase: " . $lowercase . "<br>";
        $html .= "Tokenizer: " . $tokenizer . "<br>";
        $html .= "Minimum Size: " . $minimumsize . "<br>";
        $html .= "Maximum Size: " . $maximumsize . "<br>";
        $html .= "Segment Size: " . $segmentsize . "<br>";
        $html .= "Step Size: " . $stepsize . "<br>";
        $html .= "Remove Pronouns: " . $removepronouns . "<br>";
        $html .= "Vectorspace: " . $vectorspace . "<br>";
        $html .= "Featuretype: " . $featuretype . "<br>";
        $html .= "Ngram Size: " . $ngramsize . "<br>";
        $html .= "MFI: " . $mfi . "<br>";
        $html .= "Minimum DF: " . $minimumdf . "<br>";
        $html .= "Maximum DF: " . $maximumdf;
        $html .= "</div>";

        $out->addHTML($html);
    }

}
