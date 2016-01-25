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

    private $max_length;

    public function __construct() {
        $this->max_length = 50;
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

        //check if all charachters are alphanumeric, or '/' or ':' (in case of url)
        if (!preg_match('/^[a-zA-Z0-9:\/]*$/', $input)) {
            throw new Exception('validation-charachters');
        }

        //check for empty variables or unusually long string lengths
        if (empty($input) || strlen($input) > ($this->max_length * 10)) {
            throw new Exception('validation-charlength');
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

        //check if all charachters are alphanumeric
        if (!preg_match('/^[a-zA-Z0-9]*$/', $input)) {
            throw new Exception('validation-charachters');
        }

        //check for empty variables or unusually long string lengths
        if (empty($input) || strlen($input) > ($this->max_length * 10)) {
            throw new Exception('validation-charlength');
        }

        return $input;
    }

    /**
     * This function checks if basic form conditions are met for numbers. Field specific validation is done later 
     */
    public function validateNumber($input) {

        //check if all the input consists of numbers or '.'
        if (!preg_match('/^[0-9.]*$/', $input)) {
            throw new Exception('validation-number');
        }

        if (empty($input) && $input !== '0') {
            throw new Exception('validation-empty');
        }

        if (strlen($input) > $this->max_length) {
            throw new Exception('validation-maxlength');
        }

        return $input;
    }

}
