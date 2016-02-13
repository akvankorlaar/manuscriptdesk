<?php

//there should be a base formdatagetter........
class CollateFormDataGetter {

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
        $data = $this->loadForm1Data();
        $this->checkForm1($data);
        return $data;
    }

    private function loadForm1Data() {

        $request = $this->request;
        $validator = $this->validator;

        $posted_names = $request->getValueNames();

        $manuscript_urls = array();
        $collection_data = array();
        $collection_titles = array();

        foreach ($posted_names as $key => $checkbox) {

            //remove the numbers from $checkbox
            $checkbox_without_numbers = trim(str_replace(range(0, 9), '', $checkbox));

            if ($checkbox_without_numbers === 'manuscripts_urls') {
                $manuscript_urls[$checkbox] = $validator->validateStringUrl($request->getText($checkbox));
            }
            elseif ($checkbox_without_numbers === 'collection_urls') {
                $collection_urls[$checkbox] = $validator->validateStringUrl(json_decode($request->getText($checkbox)));
            }
            elseif ($checkbox_without_numbers === 'collection_hidden') {
                $collection_titles[$checkbox] = $validator->validateString($request->getText($checkbox));
            }
        }

        return array($manuscript_urls, $collection_data, $collection_titles);
    }

    private function checkForm1(array $data) {

        global $wgCollationOptions;

        $minimum_manuscripts = $wgCollationOptions['wgmin_collation_pages'];
        $maximum_manuscripts = $wgCollationOptions['wgmax_collation_pages'];

        if (count($data) !== 3) {
            throw new \Exception('collate-error-internal');
        }

        list($manuscript_urls, $collection_data, $collection_titles) = $data;

        //check if the user has selected too few pages
        if (count($manuscript_urls) + count($collection_data) < $minimum_manuscripts) {
            throw new \Exception('collate-error-fewtexts');
        }

        $total_collection_urls = 0;

        foreach ($collection_data as $collection_name => $single_collection_urls) {
            $total_collection_urls += count($single_collection_urls);
        }

        //check if the user has selected too many pages
        if (count($manuscript_urls) + $total_collection_urls > $maximum_manuscripts) {
            throw new \Exception('collate-error-manytexts');
        }
    }

    private function temp() {

//            }elseif($checkbox_without_numbers === 'time'){
//        $this->time_identifier = $this->validateInput($request->getText('time'));
//                
//      }elseif($checkbox_without_numbers === 'save_current_table'){
//        $this->save_table = true;
//       
//      }elseif($checkbox_without_numbers === 'redirect_to_start'){
//        $this->redirect_to_start = true; 
//        break; 
//      }
    }

}
