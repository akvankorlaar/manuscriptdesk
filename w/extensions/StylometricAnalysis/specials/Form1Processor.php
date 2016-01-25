<?php

class Form1Processor {

    private $request;
    private $validator;

    public function __construct(Request $request, ManuscriptDeskBaseValidator $validator) {
        $this->request = $request;
        $this->validator = $validator;
    }

    /**
     * This function processes form 1
     */
    public function processForm1() {
        global $wgStylometricAnalysisOptions;
        $minimum_collections = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];
        $maximum_collections = $wgStylometricAnalysisOptions['wgmax_stylometricanalysis_collections'];
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

    /**
     * This function checks form 1
     */
    private function checkForm1(array $collection_array, $minimum_collections, $maximum_collections) {

        if (empty($collection_array)) {
            throw new Exception('stylometricanalysis-error-request');
        }

        if (count($this->collection_array) < $minimum_collections) {
            throw new Exception('stylometricanalysis-error-fewcollections');
        }

        if (count($this->collection_array) > $maximum_collections) {
            throw new Exception('stylometricanalysis-error-manycollections');
        }

        return true;
    }

}
