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

class Form1Processor {

    private $request;
    private $validator;

    public function __construct(WebRequest $request, ManuscriptDeskBaseValidator $validator) {
        $this->request = $request;
        $this->validator = $validator;
    }

    /**
     * This function processes form 1
     */
    public function processForm1($minimum_collections, $maximum_collections) {
        global $wgStylometricAnalysisOptions;
        $collection_array = $this->loadForm1();
        $this->checkForm1($collection_array, $minimum_collections, $maximum_collections);
        return $collection_array;
    }

    /**
     * This function loads the variables in Form 1
     */
    private function loadForm1() {

        $request = $this->request;
        $validator = $this->validator;
        $posted_names = $request->getValueNames();
        $collection_array = array();

        //identify the button pressed
        foreach ($posted_names as $key => $checkbox) {
            //remove the numbers from $checkbox to see if it matches to 'collection'
            $checkbox_without_numbers = trim(str_replace(range(0, 9), '', $checkbox));

            if ($checkbox_without_numbers === 'collection') {
                $collection_array[$checkbox] = (array) $validator->validateStringUrl(json_decode($request->getText($checkbox)));
            }
        }

        return $collection_array;
    }

    private function checkForm1(array $collection_array, $minimum_collections, $maximum_collections) {

        if (empty($collection_array)) {
            throw new Exception('stylometricanalysis-error-request');
        }

        if (count($collection_array) < $minimum_collections) {
            throw new Exception('stylometricanalysis-error-fewcollections');
        }

        if (count($collection_array) > $maximum_collections) {
            throw new Exception('stylometricanalysis-error-manycollections');
        }

        return true;
    }
}