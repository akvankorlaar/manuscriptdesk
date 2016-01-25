<?php

class Form2Processor{
	
  private $request;
  private $validator; 
  
  public function __construct(Request $request, ManuscriptDeskBaseValidator $validator){
	$this->request = $request;
	$this->validator = $validator; 
  }	
	
  /**
   * This function processes form 2
   */
  public function processForm2(){
	global $wgStylometricAnalysisOptions;   
	$min_mfi = $wgStylometricAnalysisOptions['min_mfi'];     
    $config_array = $this->loadForm2();
    $this->checkForm2($config_array, $min_mfi);
    return $config_array;       
  }	
  
  /**
   * This function loads the variables in Form 2
   */
  private function loadForm2(){
      
    $validator = $this->validator;   
    $request = $this->request;
	$config_array = array();
                               
    $config_array['removenonalpha'] = $request->getText('wpremovenonalpha');
	$conofig_array['removenonalpha'] = empty($config_array['removenonalpha']) ? 0 : 1; 

    $config_array['lowercase'] = $request->getText('wplowercase');
	$config_array['lowercase'] = empty($config_array['lowercase']) ? 0 : 1;

    $config_array['tokenizer'] = $validator->validateString($request->getText('wptokenizer'));
    $config_array['minimumsize'] = (int)$validator->validateNumber($request->getText('wpminimumsize'));
    $config_array['maximumsize'] = (int)$validator->validateNumber($request->getText('wpmaximumsize'));
    $config_array['segmentsize'] = (int)$validator->validateNumber($request->getText('wpsegmentsize'));
    $config_array['stepsize'] = (int)$validator->validateNumber($request->getText('wpstepsize'));
	
    $config_array['removepronouns'] = $request->getText('wpremovepronouns');
	$config_array['removepronouns'] = empty($config_array['removepronouns']) ? 0 : 1;

    $config_array['vectorspace'] = $validator->validateString($request->getText('wpvectorspace'));
    $config_array['featuretype'] = $validator->validateString($request->getText('wpfeaturetype'));

    $config_array['ngramsize'] = (int)$validator->validateNumber($request->getText('wpngramsize'));
    $config_array['mfi'] = (int)$validator->validateNumber($request->getText('wpmfi'));
    $config_array['minimumdf'] = floatval($validator->validateNumber($request->getText('wpminimumdf')));
    $config_array['maximumdf'] = floatval($validator->validateNumber($request->getText('wpmaximumdf')));

    $config_array['visualization1'] = $validator->validateString($request->getText('wpvisualization1'));
    $config_array['visualization2'] = $validator->validateString($request->getText('wpvisualization2'));

    $config_array['collection_array'] = (array)$validator->validateStringUrl(json_decode($request->getText('collection_array')));

    foreach($config_array['collection_array'] as $index=>&$value){
      //cast everything in collection_array to an array
      $config_array['collection_array'][$index] = (array)$value;
    }
        
    return $config_array; 
  }
  
  /**
   * This function checks form 2 
   */
  private function checkForm2($config_array, $min_mfi){
              
    if($config_array['minimumsize'] >= $config_array['maximumsize']){
      throw new Exception('stylometricanalysis-error-minmax');   
    }
    
    if($config_array['stepsize'] > $config_array['segmentsize']){
      throw new Exception('stylometricanalysis-error-stepsizesegmentsize');   
    }
    
    if($config_array['mfi'] < $min_mfi){
      throw new Exception('stylometricanalysis-error-mfi');   
    } 
  }
}
