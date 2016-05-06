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
class ManuscriptDeskBaseValidatorTest extends MediaWikiTestCase {

    private $t;

    protected function setUp() {
        parent::setUp();
        $this->t = new ManuscriptDeskBaseValidator();
    }

    protected function tearDown() {
        unset($this->t);
        parent::tearDown();
    }

    public function testvalidateStringUrl() {
        $input = 'http://justsomeurl/to/test';
        $result = $this->t->validateStringUrl($input);
        $this->assertEquals($input, $result);
    }

    public function testvalidateStringUrl2() {
        $input = array('jus/tsome', 'arrayv/alues', 't/o', 'te/st');
        $result = $this->t->validateStringUrl($input);
        $this->assertEquals($input, $result);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateStringUrlException() {
        $input = '';
        $this->t->validateStringUrl($input);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateStringUrlException2() {
        $input = '<script>';
        $this->t->validateStringUrl($input);
    }

    public function testvalidateString() {
        $input = 'testestest';
        $result = $this->t->validateString($input);
        $this->assertEquals($input, $result);
    }

    public function testvalidateString2() {
        $input = array('just', 'some', 'array', 'values', 'to', 'test');
        $result = $this->t->validateString($input);
        $this->assertEquals($input, $result);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateStringException() {
        $input = '';
        $this->t->validateString($input);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateStringException2() {
        $input = 'test/test';
        $this->t->validateString($input);
    }

    public function testvalidateStringNumber() {
        $input = '01234567889';
        $result = $this->t->validateStringNumber($input);
        $this->assertEquals($input, $result);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateStringNumberException() {
        $input = 'a';
        $this->t->validateStringNumber($input);
    }

    /**
     * @expectedException Exception
     */
    public function validateStringNumberTestException2() {
        $input = '';
        $this->t->validateString($input);
    }

    public function testvalidateSavedCollectionMetadataField() {
        $formfield_name = 'wpmetadata_websource';
        $formfield_value = 'http://website/link';
        $result = $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
        $this->assertEquals($result, $formfield_value);
    }

    public function testvalidateSavedCollectionMetadataField2() {
        $formfield_name = 'wpmetadata_notes';
        $formfield_value = "Just a string, with some special charachters. And some other special charachters! And even more special charachters? And also these charachters ;:'";
        $result = $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
        $this->assertEquals($result, $formfield_value);
    }

    public function testvalidateSavedCollectionMetadataField3() {
        $formfield_name = 'someotherfield';
        $formfield_value = 'test';
        $result = $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
        $this->assertEquals($result, $formfield_value);
    }

    public function testvalidateSavedCollectionMetadataField4() {
        $formfield_name = 'someotherfield';
        $formfield_value = '';
        $result = $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
        $this->assertEquals($result, $formfield_value);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateSavedCollectionMetadataFieldException() {
        $formfield_name = 'wpmetadata_websource';
        $formfield_value = '<script>';
        $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateSavedCollectionMetadataFieldException2() {
        $formfield_name = 'wpmetadata_notes';
        $formfield_value = '{}';
        $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
    }

    /**
     * @expectedException Exception
     */
    public function testvalidateSavedCollectionMetadataFieldException3() {
        $formfield_name = 'someotherfield';
        $formfield_value = 'test/value';
        $this->t->validateSavedCollectionMetadataField($formfield_value, $formfield_name);
    }

}
