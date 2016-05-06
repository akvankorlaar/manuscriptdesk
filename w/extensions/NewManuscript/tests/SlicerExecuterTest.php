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
class SlicerExecuterTest extends MediaWikiTestCase {

    private $t;

    protected function setUp() {
        parent::setUp();
    }

    protected function tearDown() {
        unset($this->t);
        parent::tearDown();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-execute
     */
    public function testconstructShellCommandException() {

        $mock_paths = $this->getMockBuilder('NewManuscriptPaths')
            ->setConstructorArgs(array('Root', 'testfilename', '.extension'))
            ->setMethods(array('makeDirectoryIfItDoesNotExist', 'directoryShouldExist', 'getInitialUploadFullPath'))
            ->getMock();

        $mock_paths->setExportPaths();

        $mock_paths->expects($this->once())
            ->method('getInitialUploadFullPath')
            ->will($this->returnValue('/some/fake/path/just/for/testing/purposes'));

        $this->t = new SlicerExecuter($mock_paths);

        $this->t->execute();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-execute
     */
    public function testexecuteException() {

        $mock_paths = $this->getMockBuilder('NewManuscriptPaths')
            ->setConstructorArgs(array('Root', 'testfilename', '.extension'))
            ->setMethods(array('makeDirectoryIfItDoesNotExist', 'directoryShouldExist', 'imageUploaded', 'getInitialUploadFullPath'))
            ->getMock();

        $mock_paths->setExportPaths();

        $mock_paths->expects($this->once())
            ->method('getInitialUploadFullPath')
            ->will($this->returnValue('/some/fake/path/just/for/testing/purposes'));

        $mock_paths->expects($this->once())
            ->method('imageUploaded')
            ->will($this->returnValue(true));

        $this->t = new SlicerExecuter($mock_paths);

        $this->t->execute();
    }

}
