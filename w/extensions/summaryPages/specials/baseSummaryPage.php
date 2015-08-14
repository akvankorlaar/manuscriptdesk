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
  
  public $lowercase_alphabet; 
  public $uppercase_alphabet; 
  public $article_url;
  
  protected $button_name; //value of the button the user clicked on 
  protected $max_on_page; //maximum manuscripts shown on a page
  protected $next_page_possible;
  protected $previous_page_possible; 
  protected $is_number; 
  protected $offset; 
  
   //class constructor 
	public function __construct($page_name){
    
    global $wgNewManuscriptOptions, $wgArticleUrl; 
    
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
   * This function calls processRequest() if a request was posted, or calls showDefaultPage() if no request was posted
   */
	public function execute(){
    
    $request_was_posted = $this->loadRequest();
    
    if($request_was_posted){
      return $this->processRequest();
    }
    
    return $this->showDefaultPage();     
	}
  
  /**
   * This function processes the request if it was posted
   */
  protected function processRequest(){
                
    $title_array = $this->retrieveManuscriptTitles(); 
        
    $this->showPage($title_array);          
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
}


