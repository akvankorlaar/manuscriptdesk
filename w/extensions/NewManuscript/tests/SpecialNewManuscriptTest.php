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
class UploadNewManuscriptTest extends MediaWikiTestCase {

    private $t;
    private $context;

    protected function setUp() {
        parent::setUp();
        $faux_user = User::newFromName('Root');
        $this->context = new RequestContext;
        $this->context->setUser($faux_user);
        $faux_title = Title::newFromText('NewManuscript');
        $this->context->setTitle($faux_title);
        $this->t = $this->mockSpecialNewManuscript();
        $this->t->setContext($this->context);
        return;
    }

    protected function tearDown() {
        unset($this->t);
        unset($this->context);
        parent::tearDown();
        return;
    }

    private function mockSpecialNewManuscript() {
        $special_new_manuscript_mock = $this->getMockBuilder('SpecialNewManuscript')
            ->setConstructorArgs(array())
            ->setMethods(array('checkEditToken', 'updateDatabase', 'createNewWikiPage'))
            ->getMock();

        return $special_new_manuscript_mock;
    }

    public function testDefaultPage() {
        $this->t->execute($args = '');
        $error = $this->t->getErrorIdentifier();
        $this->assertEquals($error, null);
        return;
    }

    public function testDefaultPageCollectionNamePosted() {
        $request = $this->t->getRequest();
        $validator = new ManuscriptDeskBaseValidator();

        $mock_request_processor = $this->getMockBuilder('NewManuscriptRequestProcessor')
            ->setConstructorArgs(array($request, $validator))
            ->setMethods(array('requestWasPosted', 'addNewPagePosted', 'getCollectionTitle'))
            ->getMock();

        $mock_request_processor->expects($this->once())
            ->method('requestWasPosted')
            ->will($this->returnValue(true));

        $mock_request_processor->expects($this->once())
            ->method('addNewPagePosted')
            ->will($this->returnValue(true));

        $mock_request_processor->expects($this->once())
            ->method('getCollectionTitle')
            ->will($this->returnValue('testcollection'));

        $this->t->setRequestProcessor($mock_request_processor);
        $this->t->execute($args = '');
        $error = $this->t->getErrorIdentifier();
        $this->assertEquals($error, null);
        return;
    }

    public function testDefaultPageUserPermissionException() {
        $faux_user = User::newFromName('fakeuser');
        $this->context->setUser($faux_user);
        $this->t->setContext($this->context);
        $this->t->execute($args = '');
        $error = $this->t->getErrorIdentifier();
        $this->assertEquals($error, 'error-nopermission');
        return;
    }

    public function testDefaultpageUserTooManyUploadsException() {
        $mock_wrapper = $this->getMockBuilder('NewManuscriptWrapper')
            ->setConstructorArgs(array('Root'))
            ->setMethods(array('getNumberOfUploadsForCurrentUser'))
            ->getMock();

        $mock_wrapper->expects($this->once())
            ->method('getNumberOfUploadsForCurrentUser')
            ->will($this->returnValue(500000000000));

        $this->t->setWrapper($mock_wrapper);
        $this->t->execute($args = '');
        $error = $this->t->getErrorIdentifier();
        $this->assertEquals($error, 'newmanuscript-maxreached');
        return;
    }

    public function testUploadNewManuscriptPage() {

        $request = $this->t->getRequest();
        $validator = new ManuscriptDeskBaseValidator();
        
        $mock_request_processor = $this->getMockBuilder('NewManuscriptRequestProcessor')
            ->setConstructorArgs(array($request, $validator))
            ->setMethods(array('requestWasPosted', 'addNewPagePosted', 'loadUploadFormData'))
            ->getMock();

        $mock_request_processor->expects($this->once())
            ->method('requestWasPosted')
            ->will($this->returnValue(true));

        $mock_request_processor->expects($this->once())
            ->method('addNewPagePosted')
            ->will($this->returnValue(false));

        $mock_request_processor->expects($this->once())
            ->method('loadUploadFormData')
            ->will($this->returnValue(array('test', 'none')));

        $mock_image_validator = $this->getMockBuilder('NewManuscriptImageValidator')
            ->setConstructorArgs(array($request))
            ->setMethods(array('getAndCheckUploadedImageData'))
            ->getMock();

        $mock_image_validator->expects($this->once())
            ->method('getAndCheckUploadedImageData')
            ->will($this->returnValue(array('test/path', '.testextension')));

        $stub_paths = $this->getMockBuilder('NewManuscriptPaths')
            ->setConstructorArgs(array('Root','test','.textextnsion'))
            ->getMock();

        $stub_paths->expects($this->once())
            ->method('getFullExportPath')
            ->will($this->returnValue('fake/return/path'));

        $stub_slicer_executer = $this->getMockBuilder('SlicerExecuter')
            ->setConstructorArgs(array($stub_paths))
            ->getMock();
        
         $stub_slicer_executer->expects($this->once())
            ->method('execute');

        $this->t->setRequestProcessor($mock_request_processor);
        $this->t->setImageValidator($mock_image_validator);
        $this->t->setPaths($stub_paths);
        $this->t->setSlicerExecuter($stub_slicer_executer);

        $this->t->execute($args = '');
        $error = $this->t->getErrorIdentifier();
        $this->assertEquals($error, null);
        return;
    }

}
