<?php

/**
 * This file is part of the newManuscript extension
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
 * 
 */
class NewManuscriptWrapperTest extends MediaWikiTestCase {

    private $t;
    private $test_wrapper;

    protected function setUp() {
        parent::setUp();
        $this->test_wrapper = new DatabaseTestInserter();
        $this->test_wrapper->createManuscriptsTest();
        $this->t = new NewManuscriptWrapper('testuser');
    }

    protected function tearDown() {
        $this->test_wrapper->destroyManuscriptsTest();
        unset($this->test_wrapper);
        unset($this->t);
        parent::tearDown();
    }

    public function testgetNumberOfUploadsForCurrentUser() {
        $result = $this->t->getNumberOfUploadsForCurrentUser();
        $this->assertEquals($result, 1);
    }

    public function testgetCollectionsCurrentUser() {
        $result = $this->t->getCollectionsCurrentUser();
        $this->assertEquals(empty($result), true);
    }

    public function testcheckWhetherCurrentUserIsTheOwnerOfTheCollection() {
        $result = $this->t->checkWhetherCurrentUserIsTheOwnerOfTheCollection('test');
        $this->assertEquals($result, null);
    }

    public function testcheckCollectionDoesNotExceedMaximumPages() {
        $result = $this->t->checkCollectionDoesNotExceedMaximumPages('test');
        $this->assertEquals($result, null);
    }

    public function testgetManuscriptsTitleFromUrl() {
        $result = $this->t->getManuscriptsTitleFromUrl('test/url');
        $this->assertEquals($result, 'test');
    }

    public function testgetUserNameFromUrl() {
        $result = $this->t->getUserNameFromUrl('test/url');
        $this->assertEquals($result, 'testuser');
    }

    public function testgetCollectionTitleFromUrl() {
        $result = $this->t->getCollectionTitleFromUrl('test/url');
        $this->assertEquals($result, 'test');
    }

    public function testgetPreviousAndNextPageUrl() {
        $result = $this->t->getPreviousAndNextPageUrl('test', 'test/url');
        $this->assertEquals($result[1], null);
    }

}
