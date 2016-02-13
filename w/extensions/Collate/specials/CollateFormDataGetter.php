<?php

class CollateFormDataGetter{
    
    public function __construct(){
        
    }

  /**
   * This function loads requests when a user submits the collate form
   */
  public function loadRequest(){
    
    $request = $this->getRequest();
        
    if(!$request->wasPosted()){
      return false;  
    }
    
    $posted_names = $request->getValueNames();    
     
    //identify the button pressed
    foreach($posted_names as $key=>$checkbox){
      
      //remove the numbers from $checkbox to see if it matches to 'text', 'collection', 'collection_hidden', 'redirect_to_start', or 'save_current_table'
      $checkbox_without_numbers = trim(str_replace(range(0,9),'',$checkbox));

      if($checkbox_without_numbers === 'text'){
        $this->posted_titles_array[$checkbox] = $this->validateInput($request->getText($checkbox)); 

      }elseif($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = $this->validateInput(json_decode($request->getText($checkbox)));    
      
      }elseif($checkbox_without_numbers === 'collection_hidden'){
        $this->collection_hidden_array[$checkbox] = $this->validateInput($request->getText($checkbox));
        
      }elseif($checkbox_without_numbers === 'time'){
        $this->time_identifier = $this->validateInput($request->getText('time'));
                
      }elseif($checkbox_without_numbers === 'save_current_table'){
        $this->save_table = true;
       
      }elseif($checkbox_without_numbers === 'redirect_to_start'){
        $this->redirect_to_start = true; 
        break; 
      }
    }
    
    //return false if something went wrong during validation
    if($this->variable_not_validated === true){
      return false; 
    }
    
    if($this->redirect_to_start){
      return false; 
    }
        
    return true; 
  }
  
          //check if the user has checked too few boxes
        if (count($this->posted_titles_array) + count($this->collection_array) < $this->minimum_manuscripts) {
            return $this->showError('collate-error-fewtexts');
        }

        $collection_count = 0;

        foreach ($this->collection_array as $collection_name => $url_array) {
            $collection_count += count($url_array);
        }

        //check if the user has checked too many boxes
        if (count($this->posted_titles_array) + $collection_count > $this->maximum_manuscripts) {
            return $this->showError('collate-error-manytexts');
        }
  
}
  