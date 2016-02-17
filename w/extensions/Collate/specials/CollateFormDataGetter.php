<?php

/**
 * This file is part of the Collate extension
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
        $this->checkForm1Data($data);
        return $data;
    }

    private function loadForm1Data() {

        $request = $this->request;
        $validator = $this->validator;

        $posted_names = $request->getValueNames();

        $manuscript_urls = array();
        $manuscript_titles = array();
        $collection_urls_data = array();
        $collection_titles = array();

        foreach ($posted_names as $key => $checkbox) {

            //remove the numbers from $checkbox
            $checkbox_without_numbers = trim(str_replace(range(0, 9), '', $checkbox));

            if ($checkbox_without_numbers === 'manuscript_urls') {
                $manuscript_urls[$checkbox] = $validator->validateStringUrl($request->getText($checkbox));
            }
             elseif ($checkbox_without_numbers === 'manuscript_titles') {
                $manuscript_titles[$checkbox] = $validator->validateString(($request->getText($checkbox)));
            }
            elseif ($checkbox_without_numbers === 'collection_urls') {
                $collection_urls[$checkbox] = $validator->validateStringUrl(json_decode($request->getText($checkbox)));
            }
            elseif ($checkbox_without_numbers === 'collection_titles') {
                $collection_titles[$checkbox] = $validator->validateString($request->getText($checkbox));
            }
        }

        return array($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles);
    }

    private function checkForm1Data(array $data) {

        global $wgCollationOptions;

        $minimum_manuscripts = $wgCollationOptions['wgmin_collation_pages'];
        $maximum_manuscripts = $wgCollationOptions['wgmax_collation_pages'];

        if (count($data) !== 4) {
            throw new \Exception('collate-error-internal');
        }

        list($manuscript_urls, $manuscript_titles, $collection_urls_data, $collection_titles) = $data;

        //check if the user has selected too few pages
        if (count($manuscript_urls) + count($collection_urls_data) < $minimum_manuscripts) {
            throw new \Exception('collate-error-fewtexts');
        }

        $total_collection_urls = 0;
        foreach ($collection_urls_data as $collection_name => $single_collection_urls) {
            $total_collection_urls += count($single_collection_urls);
        }

        //check if the user has selected too many pages
        if (count($manuscript_urls) + $total_collection_urls > $maximum_manuscripts) {
            throw new \Exception('collate-error-manytexts');
        }
    }

    public function getSavePageData() {
        $validator = $this->validator;
        $request = $this->request; 
        return $validator->validateStringNumber($request->getText('time'));
    }

}
