<?php

//php phpunit.php C:\xampp\htdocs\mediawikinew\w\extensions\StylometricAnalysis\tests\phpunit\specials
//https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/

class SpecialStylometricAnalysisTest extends MediaWikiTestCase {

    private $t;
    private $context;

    protected function setUp() {
        parent::setUp();

        $faux_user = User::newFromName('Root');
        $this->context = new RequestContext;
        $this->context->setUser($faux_user);
        $faux_title = Title::newFromText('StylometricAnalysis');
        $this->context->setTitle($faux_title);
        $this->t = $this->mockStylometricAnalysis();
        $this->t->setContext($this->context);
    }

    protected function tearDown() {
        unset($this->t);
        parent::tearDown();
    }

    private function mockStylometricAnalysis() {
        $mockStylometricAnalysis = $this->getMockBuilder('SpecialStylometricAnalysis')
            ->setConstructorArgs(array())
            ->setMethods(array('checkEditToken', 'callPystyl', 'checkPystylOutput', 'updateDatabase', 'showResult'))
            ->getMock();

        return $mockStylometricAnalysis;
    }

    public function testDefaultPage() {
        $this->assertEquals($this->t->execute(), true);
    }

    /**
     * @dataProvider getFakeForm1Data
     */
    public function testFakeForm1($fake_formdata) {
        $this->setRequest($fake_formdata);
        //this data throws an internal exception, but should be handled 
        $this->assertEquals($this->t->execute(), true);
    }

    /**
     * @dataProvider getFakeForm1ExceptionData
     */
    public function testFakeForm1Exceptions($fake_formdata) {
        $this->setRequest($fake_formdata);
        $this->assertEquals($this->t->execute(), false);
    }

    /**
     * @dataProvider getFakeForm2Data
     */
    public function testFakeForm2($fake_formdata) {
        $this->setRequest($fake_formdata);
        $this->assertEquals($this->t->execute(), true);
    }

    /**
     * @dataProvider getFakeForm2ExceptionData
     */
    public function testFakeForm2Exceptions($fake_formdata) {
        $this->setRequest($fake_formdata);
        $this->assertEquals($this->t->execute(), false);
    }

    /**
     * @dataProvider getFakeSaveData
     */
    public function testFakeSavePage($fake_savedata) {
        $this->setRequest($fake_savedata);
        $this->assertEquals($this->t->execute, true);
    }

    /**
     * @dataProvider getFakeSaveExceptionData
     */
    public function testFakeSavePageExceptions($fake_savedata) {
        $this->setRequest($fake_savedata);
        $this->assertEquals($this->t->execute, false);
    }

    private function setRequest(array $data) {
        $faux_request = new FauxRequest($data, true);
        $this->context->setRequest($faux_request);
    }

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
              'collection_array' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
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
              'collection_array' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
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
              'collection_array' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
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
              'collection_array' => '{"collection0":{"0":"Manuscripts:Root\/test1","1":"Manuscripts:Root\/testpage2","2":"Manuscripts:Root\/testpage3","collection_name":"collection1"},"collection1":{"0":"Manuscripts:Root\/test2","1":"Manuscripts:Root\/bla","2":"Manuscripts:Root\/bla2","collection_name":"collection2"}}',
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
