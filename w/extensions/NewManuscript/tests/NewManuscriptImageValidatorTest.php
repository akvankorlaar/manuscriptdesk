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
 * 
 */
class NewManuscriptImageValidatorTest extends MediaWikiTestCase {

    private $t;

    protected function setUp() {
        parent::setUp();
        $request = new WebRequest();
        $this->t = new NewManuscriptImageValidator($request);
        return;
    }

    protected function tearDown() {
        unset($this->t);
        parent::tearDown();
        return;
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-noimage
     */
    public function testErrorNoImageException() {
        $this->t->getAndCheckUploadedImageData();
        $this->expectException(Exception::NewManuscriptImageValidator);
    }

    public function testgetUploadBaseObject() {
        $result = $this->invokeMethod($this->t, 'getUploadBaseObject');
        $this->assertEquals($result instanceof UploadBase, true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-toolarge
     */
    public function testFileSizeException() {

        $stub = $this->getMockBuilder('concreteUploadBase')
            ->setMethods(array())
            ->getMock();

        $stub->expects($this->once())
            ->method('getFileSize')
            ->will($this->returnValue(100000000000000000000000000));

        $this->t->setUploadBase($stub);

        $result = $this->invokeMethod($this->t, 'getUploadBaseObject');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-request
     */
    public function testgetFileNameException() {

        $stub = $this->getMockBuilder('concreteUploadBase')
            ->setMethods(null)
            ->getMock();

        $result = $this->invokeMethod($this->t, 'getFileName', array($stub));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-noextension
     */
    public function testgetExtensionException() {

        $result = $this->invokeMethod($this->t, 'getExtension', array('somefilewithoutextension'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-fileformat
     */
    public function testgetExtensionFileFormatException() {

        $result = $this->invokeMethod($this->t, 'getExtension', array('file.fakeextension'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-nofile
     */
    public function testgetTempPathException() {

        $stub = $this->getMockBuilder('concreteUploadBase')
            ->setMethods(null)
            ->getMock();

        $result = $this->invokeMethod($this->t, 'getTempPath', array($stub));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-fileformat
     */
    public function testgetGuessedMimeTypeException() {
        $result = $this->invokeMethod($this->t, 'getGuessedMimeType', array('some/path/with/fake/extension.fakeextension'));
    }

}

/**
 * This class implements the abstract UploadBase class so that it can be stubbed and mocked for testing
 */
class concreteUploadBase extends UploadBase {

    public function initializeFromRequest(&$request) {
        return null;
    }

}
