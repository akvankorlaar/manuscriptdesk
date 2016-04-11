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
 * php phpunit.php C:\xampp\htdocs\mediawikinew\w\extensions\NewManuscript\tests
 * php -d xdebug.profiler_enable=On phpunit.php C:\xampp\htdocs\mediawikinew\w\extensions\NewManuscript\tests
 * set XDEBUG_CONFIG="idekey=netbeans-xdebug" 
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
    public function testgetExtensionException() {

        $stub = $this->getMockBuilder('concreteUploadBase')
            ->setMethods(null)
            ->getMock();

        $result = $this->invokeMethod($this->t, 'getExtension', array($stub));
    }

}

class concreteUploadBase extends UploadBase{
    
    public function initializeFromRequest(&$request){
        return null;
    }
}
