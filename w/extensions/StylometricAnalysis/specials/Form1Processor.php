<?php

class Form1Processor{
    
  public $request;
  public $minimum_collections;
  public $maximum_collections; 
    
  public function __construct(Request $request, $minimum_collections, $maximum_collections){
    $this->request = $request; 
    $this->minimum_collections = $minimum_collections;
    $this->maximum_collections = $maximum_collections;
  }
    
  /**
   * This function processes form 1
   */
  public function processForm1(){  
    $validator = new ManuscriptDeskBaseValidator();     
    $this->loadForm1($validator);
    $this->checkForm1();
    return true;       
  }
  
  /**
   * This function loads the variables in Form 1
   */
  private function loadForm1(ManuscriptDeskBaseValidator $validator){
      
    $request = $this->request;  
    $posted_names = $request->getValueNames();  
     
    //identify the button pressed
    foreach($posted_names as $key=>$checkbox){  
      //remove the numbers from $checkbox to see if it matches to 'collection'
      $checkbox_without_numbers = trim(str_replace(range(0,9),'',$checkbox));

      if($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = (array)$validator->validateStringUrl(json_decode($request->getText($checkbox)));                      
      }   
    }
     
    return true;   
  }
  
  /**
   * This function checks form 1
   */
  private function checkForm1(){
      
    if(count($this->collection_array) < $this->minimum_collections){        
      throw new Exception('stylometricanalysis-error-fewcollections');   
    }

    if(count($this->collection_array) > $this->maximum_collections){
      throw new Exception('stylometricanalysis-error-manycollections');   
    }
    
    return true; 
  }   
}

