<?php
/**
 * This file is part of the newManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar 'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */

class baseSummaryPage extends SpecialPage {
  
  /**
   * This class provides a set of methods to several classes with very similiar functions (SpecialAllCollations, SpecialAllCollections, SpecialAllManuscriptPages)
   * 
   * @var type 
   */
  
  public $lowercase_alphabet; 
  public $uppercase_alphabet; 
  public $article_url;
  
  protected $page_name;
  protected $button_name; //value of the button the user clicked on 
  protected $max_on_page; //maximum manuscripts shown on a page
  protected $next_page_possible;
  protected $previous_page_possible; 
  protected $is_number; 
  protected $offset; 
  protected $next_offset; 
  protected $selected_collection;
  
   //class constructor 
  public function __construct($page_name){
    
    global $wgNewManuscriptOptions, $wgArticleUrl; 
    
    $this->page_name = $page_name; 
    
    $this->article_url = $wgArticleUrl; 
    
    $this->max_on_page = $wgNewManuscriptOptions['max_on_page'];
        
    $this->next_page_possible = false;//default value
    $this->previous_page_possible = false;//default value
    
    $this->is_number = false; //default value
    
    $numbers = array('0','1','2','3','4','5','6','7','8','9');
                
    //there is both a lowercase alphabet, and a uppercase alphabet, because the lowercase alphabet is used for the database query, and the uppercase alphabet
    //for the button values
    $this->lowercase_alphabet = array_merge(range('a','z'),$numbers); 
    $this->uppercase_alphabet = array_merge(range('A','Z'),$numbers);
    
    $this->offset = 0; //default value
    
    parent::__construct($page_name);
  }
  
  /**
   * This function loads requests when a user selects a letter, moves to the previous page, or to the next page
   */
  protected function loadRequest(){
    
    $request = $this->getRequest();
        
    if(!$request->wasPosted()){
      return false;  
    }
    
    $lowercase_alphabet = $this->lowercase_alphabet;  
    $uppercase_alphabet = $this->uppercase_alphabet; 
    $posted_names = $request->getValueNames();    
     
    //identify the button pressed, and assign $posted_names to values
    foreach($posted_names as $key=>$value){
      //get the posted button
      
      if(in_array($value,$lowercase_alphabet)){
        $this->button_name = strval($value);
        
      }elseif ($value === 'singlecollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'singlecollection';
        
      //get offset, if it is available. The offset specifies at which place in the database the query should begin relative to the start 
      }elseif ($value === 'offset'){
        $string = $request->getText($value);      
        $int = (int)$string;
      
        if($int >= 0){
          $this->offset = $int;             
        }else{
          return false; 
        }        
      }
    }
    
    //if there is no button, there was no correct request
    if(!isset($this->button_name)){
      return false;
    }  
    
    if($this->offset >= $this->max_on_page){
      $this->previous_page_possible = true; 
    }
    
    if(is_numeric($this->button_name)){
      $this->is_number = true; 
    }
    
    return true; 
  }
  
  /**
   * This function validates input sent by the client
   * 
   * @param type $input
   */
  private function validateInput($input){
    
    //check for empty variables or unusually long string lengths
    if(!ctype_alnum($input) || $input === null || strlen($input) > 500){
      return false; 
    }
    
    return $input; 
  }
  
  /**
   * This function calls processRequest() if a request was posted, or calls showDefaultPage() if no request was posted
   */
  public function execute(){
    
    $request_was_posted = $this->loadRequest();
        
    if($request_was_posted){
      return $this->processRequest();
    }
        
    //show the page without processing the request
    return $this->showDefaultPage($this->getAlphabetNumbers()); 
  }
  
  /**
   * This function returns the number of pages contained in each letter or digit
   */
  protected function getAlphabetNumbers(){
      
    $summary_page_wrapper = new summaryPageWrapper($this->page_name);  
              
    //retrieve data from the database wrapper
    return $summary_page_wrapper->retrieveAlphabetNumbers();
  }
  
  /**
   * This function processes the request if it was posted
   */
  protected function processRequest(){
                   
    //get the next letter of the alphabet
    $next_letter_alphabet = $this->getNextLetter();
    
    if($this->button_name !== 'singlecollection'){   
      //intiialize the database wrapper
      $summary_page_wrapper = new summaryPageWrapper($this->page_name, $this->max_on_page, $this->offset,"", $this->button_name, $next_letter_alphabet);
    
      //retrieve data from the database wrapper
      list($title_array, $this->next_offset, $this->next_page_possible) = $summary_page_wrapper->retrieveFromDatabase();
      
    }else{    
      $summary_page_wrapper = new summaryPageWrapper('singlecollection', 0, 0,"","","", $this->selected_collection);
      $single_collection_data = $summary_page_wrapper->retrieveFromDatabase(); 
      return $this->showSingleCollectionData($single_collection_data);
    }
                
    //show the page
    $this->showPage($title_array, $summary_page_wrapper->retrieveAlphabetNumbers());          
  }
  
  /**
   * This function gets the next letter of the alphabet
   * 
   * @return string
   */
  protected function getNextLetter(){
    
    $button_name = $this->button_name; 
    $lowercase_alphabet = $this->lowercase_alphabet;
    $next_letter = null; 
    
    $index = array_search($button_name,$lowercase_alphabet);
    
    if($index !== false){
      $next_letter = isset($lowercase_alphabet[$index+1]) ? $lowercase_alphabet[$index+1] : null; 
    }
    
    if($next_letter){
      return $next_letter; 
    }

    //current letter is 'z', so do not set an upper limit
    return ''; 
  }
  
  /**
   * This function adds html used for the summarypage loader (see ext.summarypageloader)
   */
  protected function addSummaryPageLoader(){
        
    //shows after submit has been clicked
    $html  = "<h3 id='summarypage-loaderdiv' style='display: none;'>Loading";
    $html .= "<span id='summarypage-loaderspan'></span>";
    $html .= "</h3>";
    
    return $html; 
  }
}