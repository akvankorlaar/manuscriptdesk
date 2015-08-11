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

class SpecialallCollections extends SpecialPage {
  
/**
 * SpecialallCollections page. Organises all collections 
 */
  
  public $lowercase_alphabet; 
  public $uppercase_alphabet; 
  public $article_url;
  
  private $button_name; //value of the button the user clicked on 
  private $max_on_page; //maximum manuscripts shown on a page
  private $next_page_possible;
  private $previous_page_possible;   
  private $offset; 
  
   //class constructor 
	public function __construct(){
    
    global $wgNewManuscriptOptions, $wgArticleUrl; 
    
    $this->article_url = $wgArticleUrl; 
    
    $this->max_on_page = $wgNewManuscriptOptions['max_on_page'];
        
    $this->next_page_possible = false;//default value
    $this->previous_page_possible = false;//default value
    
    $numbers = array('0','1','2','3','4','5','6','7','8','9');
                
    //there is both a lowercase alphabet, and a uppercase alphabet, because the lowercase alphabet is used for the database query, and the uppercase alphabet
    //for the button values
    $this->lowercase_alphabet = array_merge(range('a','z'),$numbers); 
    $this->uppercase_alphabet = array_merge(range('A','Z'),$numbers);
    
    $this->offset = 0; //default value
    
		parent::__construct('allCollections');
	}
  
  /**
   * This function loads requests when a user selects a letter, moves to the previous page, or to the next page
   */
  private function loadRequest(){
    
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
  private function processRequest(){
            
    $title_array = $this->retrieveManuscriptTitles();
    
    $this->showPage($title_array);          
  }
  
  /**
   * This function prepares the database configuration settings, and then calls the database to fetch manuscript titles
   * 
   * @return type an array of all manuscripts
   */
  private function retrieveManuscriptTitles(){
            
    $dbr = wfGetDB(DB_SLAVE);
    
    $button_name = $this->button_name;    
    $next_letter_alphabet = $this->getNextLetter();
                           
    $conds = array(
    'manuscripts_lowercase_collection >= ' . $dbr->addQuotes($button_name),
    'manuscripts_lowercase_collection < '  . $dbr->addQuotes($next_letter_alphabet),
    'manuscripts_lowercase_collection != ' . $dbr->addQuotes("none"),
    );
             
    return $this->retrieveFromDatabase($dbr,$conds);    
  }
  
  /**
   * This function gets the next letter of the alphabet
   * 
   * @return string
   */
  private function getNextLetter(){
    
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
   * This function retrieves titles from the wiki database
   * 
   * @return type
   */
  private function retrieveFromDatabase($dbr,$conds, $title_array = array()){
    
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title', //values
        'manuscripts_user',
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_collection',
        'manuscripts_lowercase_collection',
        ), 
      $conds, //conditions
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_collection',
        'LIMIT' => $this->max_on_page+1,
        'OFFSET' => $this->offset, 
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
        
        //add titles to the title array as long as it is not bigger than max_on_page
        if (count($title_array) < $this->max_on_page){
          
          $title_array[] = array(
          'manuscripts_title' => $s->manuscripts_title,
          'manuscripts_user' => $s->manuscripts_user,
          'manuscripts_url' => $s->manuscripts_url,
          'manuscripts_date' => $s->manuscripts_date,
          'manuscripts_collection' => $s->manuscripts_collection,
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $this->next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
  return $title_array;   
  }
    
  /**
   * This function shows the page after a request has been processed
   * 
   * @param type $title_array
   */
  private function showPage($title_array){
    
    $out = $this->getOutput(); 
    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollections-title'));
    
    $html ='<form action="' . $article_url . 'Special:AllCollections" method="post">';

    //make a list of buttons that have as value a letter of the alphabet
    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 

    foreach($uppercase_alphabet as $key=>$value){
      $name = $lowercase_alphabet[$key]; 

      if($this->button_name === $name){
        $html .= "<input type='submit' name='$name' id='active_button' value='$value'>";
      }else{
        $html .= "<input type='submit' name='$name' id='letter_button' value='$value'>";
      }
    }

    $html .= '</form>';
        
    if(empty($title_array)){
      
      $out->addHTML($html);

      return $out->addWikiText($this->msg('allcollections-nocollections'));
    }
    
    if($this->previous_page_possible){
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
      
      $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
      $previous_message = $this->msg('allmanuscriptpages-previous');
      
      $html .='<form action="' . $article_url . 'Special:AllCollections" method="post">';
       
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' id = 'button' name = 'redirect_page_back' id='button' title='$previous_message_hover'  value='$previous_message'>";
      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_message_hover = $this->msg('allmanuscriptpages-nexthover');    
      $next_message = $this->msg('allmanuscriptpages-next');
      
      $html .='<form action="' . $article_url . 'Special:AllCollections" method="post">';
            
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>"; 
      $html .= "<input type='submit' id = 'button' name = 'redirect_page_forward' id='button' title='$next_message_hover' value='$next_message'>";
      
      $html.= "</form>";
    }
        
    $out->addHTML($html);
    
    $created_message = $this->msg('allmanuscriptpages-created');
    $on_message = $this->msg('allmanuscriptpages-on');
    
    $displayed_collections = array();
    $wiki_text = "";
    
    foreach($title_array as $key=>$array){
      
      $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
      $user = isset($array['manuscripts_user']) ? $array['manuscripts_user'] : '';
      $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
      $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
      $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
      
      if(in_array($collection, $displayed_collections)){
          $wiki_text .= '<br><br>[[' . $url . '|' . $title .']] <br>' . $created_message . ' ' . $user .  '<br> ' . $on_message . $date;
          
      }else{
          $wiki_text .= '<br><br>' . "'''" . $collection . ':' . "'''" . '<br><br>' . '[[' . $url . '|' . $title .']] <br>' . $created_message . ' ' . $user .  '<br> ' . $on_message . $date;
          $displayed_collections[] = $collection; 
      }             
    }
    
    $out->addWikiText($wiki_text);      
    
    return true; 
  }
  
  /**
   * This function shows the default page if no request was posted 
   */
  private function showDefaultPage(){
      
    $out = $this->getOutput();
    
    $article_url = $this->article_url; 
        
    $out->setPageTitle($this->msg('allcollections-title'));    
    
    $html ='<form action="' . $article_url . 'Special:AllCollections" method="post">';

    //make a list of buttons that have as value a letter of the alphabet
    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 
    
    foreach($uppercase_alphabet as $key=>$value){
      $name = $lowercase_alphabet[$key]; 
      $html .="<input type='submit' name='$name' id='initial_button' value='$value'>";
    } 
    
    $html .= '</form><br>';
    
    $out->addHTML($html);
    
    return $out->addWikiText($this->msg('allcollections-instruction'));
  }
}


