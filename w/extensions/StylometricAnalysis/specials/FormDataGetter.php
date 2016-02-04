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
class FormDataGetter {

    public $request;
    private $validator;

    public function __construct(WebRequest $request, ManuscriptDeskBaseValidator $validator) {
        $this->request = $request;
        $this->validator = $validator;
    }

    /**
     * This function processes form 1
     */
    public function getForm1Data() {
        global $wgStylometricAnalysisOptions;
        $minimum_collections = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];
        $maximum_collections = $wgStylometricAnalysisOptions['wgmax_stylometricanalysis_collections'];
        $collection_array = $this->loadForm1Data();
        $this->checkForm1($collection_array, $minimum_collections, $maximum_collections);
        return $collection_array;
    }

    /**
     * This function loads the variables in Form 1
     */
    private function loadForm1Data() {

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

    public function getForm2CollectionArray() {
        $validator = $this->validator;
        $request = $this->request;
        $collection_array = (array) $validator->validateStringUrl(json_decode($request->getText('collection_array')));

        foreach ($collection_array as $index => &$value) {
            //cast everything in collection_array to an array
            $collection_array[$index] = (array) $value;
        }

        return $collection_array;
    }

    public function getForm2PystylConfigurationData() {
        global $wgStylometricAnalysisOptions;
        $min_mfi = $wgStylometricAnalysisOptions['min_mfi'];
        $config_array = $this->loadForm2PystylConfigurationData();
        $this->checkForm2PystylConfigurationData($config_array, $min_mfi);
        return $config_array;
    }

    /**
     * This function loads the config array of Form 2 (data that will be sent to PyStyl)
     */
    private function loadForm2PystylConfigurationData() {
        $validator = $this->validator;
        $request = $this->request;
        $config_array = array();

        $config_array['removenonalpha'] = $request->getText('wpremovenonalpha');
        $config_array['removenonalpha'] = empty($config_array['removenonalpha']) ? 0 : 1;

        $config_array['lowercase'] = $request->getText('wplowercase');
        $config_array['lowercase'] = empty($config_array['lowercase']) ? 0 : 1;

        $config_array['tokenizer'] = $validator->validateString($request->getText('wptokenizer'));
        $config_array['minimumsize'] = (int) $validator->validateNumber($request->getText('wpminimumsize'));
        $config_array['maximumsize'] = (int) $validator->validateNumber($request->getText('wpmaximumsize'));
        $config_array['segmentsize'] = (int) $validator->validateNumber($request->getText('wpsegmentsize'));
        $config_array['stepsize'] = (int) $validator->validateNumber($request->getText('wpstepsize'));

        $config_array['removepronouns'] = $request->getText('wpremovepronouns');
        $config_array['removepronouns'] = empty($config_array['removepronouns']) ? 0 : 1;

        $config_array['vectorspace'] = $validator->validateString($request->getText('wpvectorspace'));
        $config_array['featuretype'] = $validator->validateString($request->getText('wpfeaturetype'));

        $config_array['ngramsize'] = (int) $validator->validateNumber($request->getText('wpngramsize'));
        $config_array['mfi'] = (int) $validator->validateNumber($request->getText('wpmfi'));
        $config_array['minimumdf'] = floatval($validator->validateNumber($request->getText('wpminimumdf')));
        $config_array['maximumdf'] = floatval($validator->validateNumber($request->getText('wpmaximumdf')));

        $config_array['visualization1'] = $validator->validateString($request->getText('wpvisualization1'));
        $config_array['visualization2'] = $validator->validateString($request->getText('wpvisualization2'));

        return $config_array;
    }

    private function checkForm2PystylConfigurationData($config_array, $min_mfi) {

        if ($config_array['minimumsize'] >= $config_array['maximumsize']) {
            throw new Exception('stylometricanalysis-error-minmax');
        }

        if ($config_array['stepsize'] > $config_array['segmentsize']) {
            throw new Exception('stylometricanalysis-error-stepsizesegmentsize');
        }

        if ($config_array['mfi'] < $min_mfi) {
            throw new Exception('stylometricanalysis-error-mfi');
        }
    }

    public function getSavePageData() {
        $request = $this->request;
        $validator = $this->validator;
        $time = $validator->validateNumber(json_decode($request->getText('time')));
        return $time; 
    }

}
