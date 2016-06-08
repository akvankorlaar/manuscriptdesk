<?php

/**
 * This file is part of the NewManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */
class NewManuscriptPathsTest extends MediaWikiTestCase {

    private $t;

    protected function setUp() {
        parent::setUp();
        $this->t = $this->mockNewManuscriptPaths();
        return;
    }

    protected function tearDown() {
        unset($this->t);
        parent::tearDown();
        return;
    }

    private function mockNewManuscriptPaths() {
        $mock = $this->getMockBuilder('NewManuscriptPaths')
            ->setConstructorArgs(array('Root', 'testfilename', '.extension'))
            ->setMethods(array('makeDirectoryIfItDoesNotExist', 'directoryShouldExist'))
            ->getMock();

        return $mock;
    }

    public function testsetInitialUploadFullPath() {
        $this->t->setOriginalImagesFullPath();
        $full_path = $this->t->getOriginalImagesFullPath();
        $this->assertEquals(is_string($full_path), true);
    }

    public function testinitialUploadFullPathIsConstructableFromScan() {
        $result = $this->t->originalImagesFullPathIsConstructableFromScan();
        $this->assertEquals($result, false);
    }

    public function testExportPaths() {
        $this->t->setExportPaths();
        $full_path = $this->t->getFullExportPath();
        $this->assertEquals(is_string($full_path), true);
    }

    public function testsetPartialUrl() {
        $this->t->setPartialUrl();
        $partial_url = $this->t->getPartialUrl();
        $this->assertEquals(is_string($partial_url), true);
    }

    public function getPerlPath() {
        $path = $this->t->getPerlPath();
        $this->assertEquals(is_dir($path), true);
    }

    public function getSlicerPath() {
        $path = $this->t->getSlicerPath();
        $this->assertEquals(file_exists($path), true);
    }

    public function testgetWebLinkInitialUploadPath() {
        $path = $this->t->getWebLinkOriginalImagesPath();
        $this->assertEquals(is_string($path), true);
    }

    public function getWebLinkExportPath() {
        $path = $this->t->getWebLinkExportPath();
        $this->assertEquals(is_string($path), true);
    }

}
