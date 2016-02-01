<?php

//// * Transfer data from tempstylometricanalysis -> stylometricanalysis table
//// * Delete old entries tempstylometricanalysis table
//// * Make new page with appropriate data
//// * Redirect User      

class FormDataGetterTest extends MediaWikiTestCase {

    private $t;

    protected function setUp() {
        parent::setUp();

        $faux_request = new FauxRequest(array(), false);
        $validator = new ManuscriptDeskBaseValidator();

        $this->t = new FormDataGetter($faux_request, $validator);
    }

    protected function tearDown() {
        unset($this->t);
        parent::tearDown();
    }

    public function testgetForm1Data() {
        //dataprovider for form1 .... 
        $output = $this->t->getForm1Data();
        //assert count ... 
    }
    
    public function testgetForm2Data(){
        //dataprovider for form2 .... 
        $output = $this->t->getForm2Data();
        //assert count ... 
    }
    
    
    
    
    //form1 and form2 with wrong data providers ... 
    
    
    
    
    
    
    

    public function testgetSavePageInformationArray() {
        $data = $this->invokeMethod($this->t, 'getSavePageInformationArray');
        $this->assertCount(3, $data);
    }
    
    /**
     * @expectedException Exception
     * @dataProvider dataProvider
     */
    public function testgetSavePageInformationArrayWrongData($data) {
        $this->t->request->setVal($data);
        $data = $this->invokeMethod($this->t, 'getSavePageInformationArray');
    }

    public function dataProvider() {

        $data = array(
          array('save_current_page' => json_encode(array('fulloutputpath1', 'fulloutputpath2', 'element3', 'element4'))),
          array('save_current_page' => json_encode(array('full_output_path1', 'fulloutputpath2', 'element3'))),
          array('save_current_page' => json_encode(array('fulloutputpath1', 'fulloutputpath2'))),
          array('save_current_page' => json_encode(array('fulloutputpath1'))),
          array('save_current_page' => json_encode(array(''))),
        );
        
        return $data;
    }

}