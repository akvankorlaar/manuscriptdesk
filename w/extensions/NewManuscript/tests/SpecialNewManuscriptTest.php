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

class UploadNewManuscriptTest extends MediaWikiTestCase{
    
    //make a fake title
    //make a fake image
    //upload it through the NewManuscript module
    //check if everything is ok
    //delete the entry
    
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
            ->setMethods(array('checkEditToken'))
            ->getMock();

        return $special_new_manuscript_mock;
    }

    public function testDefaultPage() {
        $this->t->execute($args = '');
        $error = $this->t->getErrorIdentifier();
        $this->assertEquals($error, null);               
        return;
    }
    
    public function testDefaultPageCollectionNamePosted(){
        
        $request = $this->t->getRequest();
        $validator = new ManuscriptDeskBaseValidator();
        
        $mock_request_processor = $this->getMockBuilder('NewManuscriptRequestProcessor')
            ->setConstructorArgs(array($request, $validator))
            ->setMethods(array('requestWasPosted', 'addNewPagePosted','getCollectionTitle'))
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
    
    public function testDefaultpageUserTooManyUploadsException(){
               
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
        
      

//    /**
//     * @dataProvider getFakeForm1Data
//     */
//    public function testFakeForm1($fake_formdata) {
//        $this->setRequest($fake_formdata);
//        //this data throws an internal exception, but should be handled 
//        $this->assertEquals($this->t->execute(), true);
//    }
//
//    /**
//     * @dataProvider getFakeForm1ExceptionData
//     */
//    public function testFakeForm1Exceptions($fake_formdata) {
//        $this->setRequest($fake_formdata);
//        $this->assertEquals($this->t->execute(), false);
//    }
//    
//    private function setRequest(array $data) {
//        $faux_request = new FauxRequest($data, true);
//        $this->context->setRequest($faux_request);
//    }

    public function getFakeForm1Data() {

        $form1_data = array(
          array(
            array(
              'form1Posted' => 'form1Posted',
              'collection0' => '{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"}',
              'collection1' => '{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}',
            )
          ),
        );

        return $form1_data;
    }

    public function getFakeForm1ExceptionData() {

        $form1_data = array(
          array(
            array(
              //no data
              'form1Posted' => 'form1Posted',
            )),
          array(
            array(
              //not enough data
              'form1Posted' => 'form1Posted',
              'collection1' => '{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}',
            )),
          array(
            array(
              //data with invalid charachters
              'form1Posted' => 'form1Posted',
              'collection0' => '{"0":"Manuscrip(*)&%$ts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"}',
              'collection1' => '{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}',
            )),
        );

        return $form1_data;
    }

    public function getFakeForm2Data() {

        $form2_data = array(
          array(
            array(
              'wpremovenonalpha' => '0',
              'wplowercase' => '0',
              'wptokenizer' => 'whitespace',
              'wpminimumsize' => '0',
              'wpmaximumsize' => '10000',
              'wpsegmentsize' => '0',
              'wpstepsize' => '0',
              'wpvectorspace' => 'tf',
              'wpfeaturetype' => 'word',
              'wpngramsize' => '1',
              'wpmfi' => '100',
              'wpminimumdf' => '0',
              'wpmaximumdf' => '0.9',
              'wpvisualization1' => 'dendrogram',
              'wpvisualization2' => 'dendrogram',
              'title' => 'Special:StylometricAnalysis',
              'collection_data' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
              'form2Posted' => 'form2Posted',
            )),
        );

        return $form2_data;
    }

    public function getFakeForm2ExceptionData() {

        $form2_data = array(
          //data missing
          array(
            array(
              'wpminimumsize' => '0',
              'wpmaximumsize' => '10000',
              'wpsegmentsize' => '0',
              'wpstepsize' => '0',
              'wpvectorspace' => 'tf',
              'wpfeaturetype' => 'word',
              'wpngramsize' => '1',
              'wpmfi' => '100',
              'wpminimumdf' => '0',
              'wpmaximumdf' => '0.9',
              'wpvisualization1' => 'dendrogram',
              'wpvisualization2' => 'dendrogram',
              'title' => 'Special:StylometricAnalysis',
              'collection_data' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
              'form2Posted' => 'form2Posted',
            )),
          //data with invalid charachters
          array(
            array(
              'wptokenizer' => 'whitespace',
              'wpminimumsize' => '0',
              'wpmaximumsize' => '10000',
              'wpsegmentsize' => '0',
              'wpstepsize' => '0',
              'wpvectorspace' => 'tf^&&^(*',
              'wpfeaturetype' => 'word',
              'wpngramsize' => '1',
              'wpmfi' => '100',
              'wpminimumdf' => '0',
              'wpmaximumdf' => '0.9',
              'wpvisualization1' => 'dendrogram',
              'wpvisualization2' => 'dendrogram',
              'title' => 'Special:StylometricAnalysis',
              'collection_data' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
              'form2Posted' => 'form2Posted',
            )),
          //data with invalid values
          array(
            array(
              'wptokenizer' => 'whitespace',
              'wpminimumsize' => '0',
              'wpmaximumsize' => '10000',
              'wpsegmentsize' => '0',
              'wpstepsize' => '0',
              'wpvectorspace' => 'tf',
              'wpfeaturetype' => 'word',
              'wpngramsize' => '1',
              'wpmfi' => '100',
              'wpminimumdf' => '-50',
              'wpmaximumdf' => '0.9',
              'wpvisualization1' => 'dendrogram',
              'wpvisualization2' => 'dendrogram',
              'title' => 'Special:StylometricAnalysis',
              'collection_data' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
              'form2Posted' => 'form2Posted',
            )),
          //data missing
          array(
            array(
              'form2Posted' => 'form2Posted',
            )),
          //collection array missing
          array(
            array(
              'wptokenizer' => 'whitespace',
              'wpminimumsize' => '0',
              'wpmaximumsize' => '10000',
              'wpsegmentsize' => '0',
              'wpstepsize' => '0',
              'wpvectorspace' => 'tf',
              'wpfeaturetype' => 'word',
              'wpngramsize' => '1',
              'wpmfi' => '100',
              'wpminimumdf' => '0',
              'wpmaximumdf' => '0.9',
              'wpvisualization1' => 'dendrogram',
              'wpvisualization2' => 'dendrogram',
              'title' => 'Special:StylometricAnalysis',
              'form2Posted' => 'form2Posted',
            )),
        );

        return $form2_data;
    }

    public function getFakeSaveData() {

        $data = array(
          array(
            array(
              'save_current_page' => 'save_current_page',
              'time' => '012345678987654321',
            )),
        );

        return $data;
    }

    public function getFakeSaveExceptionData() {
        
        $data = array(
          //data missing  
          array(
            array(
              'save_current_page' => 'save_current_page',
            )),
          //data missing 
          array(
            array(
              'save_current_page' => 'save_current_page',
              'time' => '',            
              )),
          //invalid charachters  
          array(
            array(
              'save_current_page' => 'save_current_page',
              'time' => '0123456789876abc54321', 
            )),
        );

        return $data;
    }

}
    
    