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
class CollatexConverter {

    public function __construct() {
        
    }

    public function execute(array $texts) {
        $json_encoded_text = $this->convertToFormatAcceptableByCollatex($texts);
        $curl = $this->constructCurlCollatexCaller($json_encoded_text);
        $result = $this->executeCurlCollatexCaller($curl);
        $this->checkForCollatexOutputErrors($result);
        return $result;
    }

    private function convertToFormatAcceptableByCollatex(array $text_array) {

        $content = array();
        $alphabet = range('A', 'Z');
        $length_text_array = count($text_array);

        for ($i = 0; $i < $length_text_array; $i++) {
            $content["witnesses"][$i]["id"] = $alphabet[$i];
            $content["witnesses"][$i]["content"] = $text_array[$i];
        }

        return json_encode($content);
    }

    private function constructCurlCollatexCaller($json_encoded_text = '') {
        global $wgCollationOptions;

        $url = $wgCollationOptions['collatex_url'];
        $headers = $wgCollationOptions['collatex_headers'];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_encoded_text = '');
        
        return $curl; 
    }

    private function executeCurlCollatexCaller($curl) {
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    private function checkForCollatexOutputErrors($result) {
        //if the output is an empty string, collatex was not started up or configured properly
        if (!$result || $result === "") {
            throw new \Exception('collate-error-collatex');
        }
    }

}
