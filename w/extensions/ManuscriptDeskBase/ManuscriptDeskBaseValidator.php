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
class ManuscriptDeskBaseValidator {

    private $max_length = 50;

    public function __construct() {
        
    }

    /**
     * This function validates strings inside an array or an object 
     */
    public function validateStringUrl($input) {

        if (is_array($input) || is_object($input)) {

            foreach ($input as $index => $value) {
                $status = $this->validateStringUrl($value);
            }

            return $input;
        }

        if (empty($input)) {
            throw new \Exception('validation-empty');
        }

        //check if all charachters are alphanumeric, or '/' or ':' (in case of url)
        if (!preg_match('/^[a-zA-Z0-9:\/]*$/', $input)) {
            throw new \Exception('validation-notalphanumeric');
        }

        //check for empty variables or unusually long string lengths
        if (strlen($input) > ($this->max_length * 10)) {
            throw new \Exception('validation-toolongstring');
        }

        return $input;
    }

    /**
     * This function validates strings inside an array or an object 
     */
    public function validateString($input) {

        if (is_array($input) || is_object($input)) {

            foreach ($input as $index => $value) {
                $status = $this->validateString($value);
            }

            return $input;
        }

        if (empty($input)) {
            throw new \Exception('validation-empty');
        }

        //check if all charachters are alphanumeric
        if (!preg_match('/^[a-zA-Z0-9]*$/', $input)) {
            throw new \Exception('validation-notalphanumeric');
        }

        if (strlen($input) > ($this->max_length * 10)) {
            throw new \Exception('validation-morethanfiftycharachters');
        }

        return $input;
    }

    /**
     * This function checks if basic form conditions are met for numbers. Field specific validation is done later 
     */
    public function validateStringNumber($input) {

        //string containting 0 is also seen as empty
        if (empty($input) && $input !== '0') {
            throw new \Exception('validation-empty');
        }

        //check if all the input consists of numbers or '.'
        if (!preg_match('/^[0-9.]*$/', $input)) {
            throw new \Exception('validation-notanumber');
        }

        if (strlen($input) > $this->max_length) {
            throw new \Exception('validation-morethanfiftycharachters');
        }

        return $input;
    }

    public function validateSavedCollectionMetadataField($formfield_value, $formfield_name) {

        $max_length = $this->max_length;

        if (empty($formfield_value)) {
            //empty metadata values are allowed
            return $formfield_value;
        }

        if ($formfield_name === 'wpmetadata_websource') {

            if (strlen($formfield_value) > $max_length) {
                throw new \Exception('validation-morethanfiftycharachters');
            }

            //allow alphanumeric charachters, whitespace, and '-./:'  
            elseif (!preg_match("/^[A-Za-z0-9\-.\/:\s]+$/", $formfield_value)) {
                throw new \Exception('validation-websourcecharachters');
            }
        }
        
        elseif ($formfield_name === 'wpmetadata_notes') {

            if (strlen($formfield_value) > ($max_length * 20)) {
                throw new \Exception('validation-noteslength');
            }
            //allow alphanumeric charachters, whitespace, and ',.;!?' 
            elseif (!preg_match("/^[A-Za-z0-9,.;!?\s]+$/", $formfield_value)) {
                throw new \Exception('validation-notescharachters');
            }
        }
        else {

            if (strlen($formfield_value) > $max_length) {
                throw new \Exception('validation-morethanfiftycharachters');
            }

            //allow alphanumeric charachters and whitespace  
            elseif (!preg_match("/^[A-Za-z0-9\s]+$/", $formfield_value)) {
                throw new \Exception('validation-metadatacharachters');
            }
        }

        return $formfield_value;
    }

}
