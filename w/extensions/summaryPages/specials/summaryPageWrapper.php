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

class summaryPageWrapper{
  
  private $page_name; 
  private $max_on_page;
  private $offset; 
  private $user_name; 
  private $button_name; 
  private $next_page_possible; 
  private $next_letter_alphabet; 
  
  //class constructor
  public function __construct($page_name, $max_on_page = 0, $offset = 0,$user_name = "", $button_name = "", $next_letter_alphabet = ""){
    
    $this->page_name = $page_name;
    $this->max_on_page = $max_on_page;
    $this->offset = $offset;
    $this->user_name = $user_name; 
    $this->button_name = $button_name; 
    $this->next_page_possible = false; //default value
    $this->next_letter_alphabet = $next_letter_alphabet; 
  }
  
  /**
   * This function decides which function to call, depending on the values given to the __constructor. 
   * 
   * The names of the functions are 'retrieve' + name of page that called this class
   * 
   * @return type
   */
  public function retrieveFromDatabase(){
    
    switch($this->page_name){
      
      case 'AllCollations':
        return $this->retrieveAllCollations();
        break;
      case 'AllCollections':
        return $this->retrieveAllCollections();
        break;
      case 'AllManuscriptPages':
        return $this->retrieveAllManuscriptPages();
        break; 
      case 'RecentManuscriptPages':
        return $this->retrieveRecentManuscriptPages();
        break;
      case 'UserPageManuscriptPages':
        return $this->retrieveUserPageManuscriptPages();
        break;  
      case 'UserPageCollations':
        return $this->retrieveUserPageCollations();
        break;
      case 'UserPageCollections':
        return $this->retrieveUserPageCollections();
        break;   
    }
  }
                 
  /**
   * This function prepares the database configuration settings, and then calls the database to fetch manuscript titles
   * 
   * @return type an array of all manuscripts
   */       
  private function retrieveAllCollations($title_array = array()){
    
    $button_name = $this->button_name; 
    $next_letter_alphabet = $this->next_letter_alphabet;
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array();  
    $next_offset = null; 

    //Database query
    $res = $dbr->select(
      'collations', //from
      array(
        'collations_user',//values
        'collations_url',
        'collations_date',
        'collations_main_title',
        'collations_main_title_lowercase'
        ), 
      array(
        'collations_main_title_lowercase >= ' . $dbr->addQuotes($button_name),
        'collations_main_title_lowercase < ' . $dbr->addQuotes($next_letter_alphabet), 
       ),
      __METHOD__,
      array(
        'ORDER BY' => 'collations_main_title_lowercase',
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
          'collations_user'       => $s->collations_user,
          'collations_url'        => $s->collations_url,
          'collations_date'       => $s->collations_date,
          'collations_main_title' => $s->collations_main_title,
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return array($title_array, $next_offset, $this->next_page_possible);   
  }
  
  /**
   * This function prepares the database configuration settings, and then calls the database to fetch collection titles and their associated manuscript pages
   * 
   * @return type an array of all manuscripts
   */
  private function retrieveAllCollections(){
                
    $button_name = $this->button_name;
    $next_letter_alphabet = $this->next_letter_alphabet; 
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array();
    $next_offset = null; 
    
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
      array(
        'manuscripts_lowercase_collection >= ' . $dbr->addQuotes($button_name),
        'manuscripts_lowercase_collection < '  . $dbr->addQuotes($next_letter_alphabet),
        'manuscripts_lowercase_collection != ' . $dbr->addQuotes("none"),
       ),
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
          $next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return array($title_array, $next_offset, $this->next_page_possible);   
  }
  
   /**
   * This function prepares the database configuration settings, and then calls the database to fetch manuscript titles
   * 
   * @return type an array of all manuscripts
   */
  private function retrieveAllManuscriptPages(){
            
    $button_name = $this->button_name;
    $next_letter_alphabet = $this->next_letter_alphabet; 
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array();
    $next_offset = null; 
                                                
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title', //values
        'manuscripts_user',
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_lowercase_title',
        ), 
      array(
    'manuscripts_lowercase_title >= ' . $dbr->addQuotes($button_name),
    'manuscripts_lowercase_title < ' . $dbr->addQuotes($next_letter_alphabet), 
     ),
    __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
        //'USE INDEX' => 'name_title', //can this still be used?
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
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return array($title_array, $next_offset, $this->next_page_possible);   
  }
  
  /**
   * This function prepares the database configuration settings, and then calls the database to fetch manuscript the most recent manuscript titles
   * 
   * @return type an array of all manuscripts
   */
  private function retrieveRecentManuscriptPages(){
            
    $max_on_page = $this->max_on_page; 
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array(); 
             
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title', //values
        'manuscripts_user',
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_collection',
        'manuscripts_lowercase_title',
        'manuscripts_datesort'
        ), 
      array(
    'manuscripts_lowercase_title >= ' . $dbr->addQuotes(""),
        ), 
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_datesort DESC',
        'LIMIT' => $this->max_on_page,
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
                  
        $title_array[] = array(
        'manuscripts_title' => $s->manuscripts_title,
        'manuscripts_user' => $s->manuscripts_user,
        'manuscripts_url' => $s->manuscripts_url,
        'manuscripts_date' => $s->manuscripts_date,
        'manuscripts_collection' => $s->manuscripts_collection,  
          );  
      }     
    }
    
    return $title_array;   
  }
  
  /**
   * This function retrieves data from the 'manuscripts' table
   * 
   * @return type
   */
  private function retrieveUserPageManuscriptPages(){
    
    $user_name = $this->user_name;
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array();
    $next_offset = null; 
    
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_collection',
        'manuscripts_lowercase_title',
         ),
      array(
        'manuscripts_user = ' . $dbr->addQuotes($user_name),  
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
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
          'manuscripts_url' => $s->manuscripts_url,
          'manuscripts_date' => $s->manuscripts_date,
          'manuscripts_collection' => $s->manuscripts_collection,
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return array($title_array, $next_offset, $this->next_page_possible);   
  }
  
  /**
   * This function retrieves data from the 'collations' table
   * 
   * @param type $dbr
   * @param type $conds
   * @param type $titles_array
   */
  private function retrieveUserPageCollations(){
    
    $user_name = $this->user_name;
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array();
    $next_offset = null; 
    
      //Database query
    $res = $dbr->select(
      'collations', //from
      array(
        'collations_url',//values
        'collations_date',
        'collations_main_title',
        'collations_main_title_lowercase'
         ),
        array(
          'collations_user = ' . $dbr->addQuotes($user_name),
          ),
      __METHOD__,
      array(
        'ORDER BY' => 'collations_main_title_lowercase',
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
          'collations_url' => $s->collations_url,
          'collations_date' => $s->collations_date,
          'collations_main_title' => $s->collations_main_title,
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return array($title_array, $next_offset, $this->next_page_possible); 
  }
  
  /**
   * This function retrieves data of manuscripts contained in collections from the 'manuscripts' table
   * 
   * @return type
   */
  private function retrieveUserPageCollections(){
    
    $user_name = $this->user_name;
    $dbr = wfGetDB(DB_SLAVE);
    $title_array = array();
    $next_offset = null; 
    
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_collection',
         ),
       array(
        'manuscripts_user = ' . $dbr->addQuotes($user_name),
        'manuscripts_collection != ' . $dbr->addQuotes(""),
        'manuscripts_collection != ' . $dbr->addQuotes("none"),
        ),
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
          'manuscripts_url' => $s->manuscripts_url,
          'manuscripts_date' => $s->manuscripts_date,
          'manuscripts_collection' => $s->manuscripts_collection,  
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return array($title_array, $next_offset, $this->next_page_possible);  
  }
}