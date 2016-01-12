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

class newManuscriptWrapper{
  
  private $user_name; 
  private $maximum_pages_per_collection; 

  //class constructor
  public function __construct($user_name = "", $maximum_pages_per_collection = null){
    
    $this->user_name = $user_name;
    $this->maximum_pages_per_collection = $maximum_pages_per_collection; 
  }
  
 /**
   * This function retrieves the collections of the current user
   *  
   * @return type
   */
  public function getCollectionsCurrentUser(){
    
    $dbr = wfGetDB(DB_SLAVE);
    
    $user_name = $this->user_name; 
    $collections_current_user = array();
    
     //Database query
    $res = $dbr->select(
       'collections', //from
     array(
       'collections_title',//values
       'collections_title_lowercase',
        ),
     array(
       'collections_user = ' . $dbr->addQuotes($user_name), //conditions
     ),
     __METHOD__,
     array(
       'ORDER BY' => 'collections_title_lowercase',
     )
     );

    //while there are results
    while ($s = $res->fetchObject()){
      
      $collections_current_user[] = $s->collections_title;    
    }    
    
    return $collections_current_user; 
  }
  
  /**
   * This functions checks if the collection already reached the maximum allowed manuscript pages, or if the current user is the creator of the collection
   * 
   * @param type $posted_collection
   * @return string
   */
  public function checkTables($posted_collection){
    
    $dbr = wfGetDB(DB_SLAVE);
         
    $res = $dbr->select(
      'manuscripts', //from
      array(
      'manuscripts_url',//values
       ),
      array(
        'manuscripts_user = ' . $dbr->addQuotes($this->user_name), //conditions
        'manuscripts_collection = ' . $dbr->addQuotes($posted_collection),
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
      )
    );
        
    if ($res->numRows() > $this->maximum_pages_per_collection){
      return 'newmanuscript-error-collectionmaxreached';
    }
        
    $res = $dbr->select(
      'collections', //from
      array( //values
      'collections_title',
      'collections_user',  
         ),
      array(
      'collections_title = ' . $dbr->addQuotes($posted_collection),
      ),
      __METHOD__,
      array(
      'ORDER BY' => 'collections_title',
      )
    );
        
    //if the user is not the owner of the collection, return an error
    if ($res->numRows() === 1){
      $s = $res->fetchObject();
      $collections_user = $s->collections_user;
      
      if($collections_user !== $this->user_name){
        return 'newmanuscript-error-notcollectionsuser';
      }
    }
   
    return ""; 
  }
  
  /**
   * This function insert data into the manuscripts table
   * 
   * @param type $posted_title
   * @param type $user_name    
   * @param type $new_page_url
   * @return boolean
   */
  public function storeManuscripts($posted_title, $collection, $user_name,$new_page_url, $date){
      
    $date2 = date('YmdHis');
    $lowercase_title = strtolower($posted_title);
    $lowercase_collection = strtolower($collection);
    
    $dbw = wfGetDB(DB_MASTER);
    
    $dbw->insert('manuscripts', //select table
      array( //insert values
      'manuscripts_id'                   => null,
      'manuscripts_title'                => $posted_title,
      'manuscripts_user'                 => $user_name,
      'manuscripts_url'                  => $new_page_url,
      'manuscripts_date'                 => $date,
      'manuscripts_lowercase_title'      => $lowercase_title,
      'manuscripts_collection'           => $collection, 
      'manuscripts_lowercase_collection' => $lowercase_collection,  
      'manuscripts_datesort'             => $date2,
       ),__METHOD__,
       'IGNORE' );
    if ($dbw->affectedRows()){
    //insert succeeded
      return true;
      
    }else{
    //return error
      return false;      
    }
  }
  
  /**
   * This function insert data into the collections table
   * 
   * @param type $collection_title
   * @param type $user_name    
   * @return boolean
   */
  public function storeCollections($collection_name, $user_name, $date){
      
    $dbw = wfGetDB(DB_MASTER);
    
    $collections_title_lowercase = strtolower($collection_name);
    
    $dbw->insert('collections', //select table
      array( //insert values
      'collections_title'                => $collection_name,
      'collections_title_lowercase'      => $collections_title_lowercase,
      'collections_user'                 => $user_name,
      'collections_date'                 => $date,  
       ),__METHOD__,
       'IGNORE' ); //ensures that duplicate $collection_name is ignored
    
     if ($dbw->affectedRows()){
      //collection did not exist yet
      return true;
      
    }else{
    //collection already exists
      return false;      
    }
  }
  
  /**
   * This function increments the alphabetnumbers table. The first letter or digit of the $posted_title is extracted, and the value is incremented in the appropriate place.
   * The alphabetnumbers table is used to visualize the number of pages in different categories (used in for example: Special:AllCollections)
   * 
   * @param type $collection_title
   * @param type $user_name    
   * @return boolean
   */
  public function storeAlphabetnumbers($posted_title, $collection_name){
      
    if($collection_name === 'none'){
      $alphabetnumbers_context = 'SingleManuscriptPages'; 
      $first_char = substr($posted_title,0,1);
    }else{
      $alphabetnumbers_context = 'AllCollections'; 
      $first_char = substr($collection_name,0,1);
    }
                
    if (preg_match('/[0-9]/',$first_char)){
        
      switch ($first_char){
        case '0':
          $first_char = 'zero';
          break;  
        case '1':
          $first_char = 'one';
          break;  
        case '2':
          $first_char = 'two';
          break;  
        case '3':
          $first_char = 'three';
          break;  
        case '4':
          $first_char = 'four';
          break;  
        case '5':
          $first_char = 'five';
          break;  
        case '6':
          $first_char = 'six';
          break;  
        case '7':
          $first_char = 'seven';
          break;  
        case '8':
          $first_char = 'eight';
          break;  
        case '9':
          $first_char = 'nine';
          break;   
        }
    }

    //first select the old value, increment it by one, and update the value. Ideally this should be done in 1 update statement, but there seems to be no other way using
    //Mediawiki's database wrapper
    $dbr = wfGetDB(DB_SLAVE);
   
    $res = $dbr->select(
      'alphabetnumbers', //from
      array( //values
      $first_char,
      ),
      array(
      'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context),
      ),
      __METHOD__
    );
            
    //there should only be 1 result
    if ($res->numRows() === 1){
      $s = $res->fetchObject();
      $intvalue = (int)(($s->$first_char)+1);
      
      $dbw = wfGetDB(DB_MASTER);

      $dbw->update(
        'alphabetnumbers', //select table
        array( //insert values
        $first_char => $intvalue,
         ),
         array(
        'alphabetnumbers_context = ' . $dbw->addQuotes($alphabetnumbers_context),
      ),
       __METHOD__
    ); 
    
      if ($dbw->affectedRows()){
        return true;
      
      }else{
        return false;      
      }    
    }
    
    return false;   
  }
  
  /**
   * This function subtracts entries in the alphabetnumbers table when one of the manuscript pages is deleted
   */
  public function subtractAlphabetnumbers($filename_fromurl, $collection_name){
    
    if($collection_name === 'none' || $collection_name === null){
      $alphabetnumbers_context = 'SingleManuscriptPages'; 
      $first_char = substr($filename_fromurl,0,1);
    }else{
      $alphabetnumbers_context = 'AllCollections'; 
      $first_char = substr($collection_name,0,1);
    }
                
    if (preg_match('/[0-9]/',$first_char)){
        
      switch ($first_char){
        case '0':
          $first_char = 'zero';
          break;  
        case '1':
          $first_char = 'one';
          break;  
        case '2':
          $first_char = 'two';
          break;  
        case '3':
          $first_char = 'three';
          break;  
        case '4':
          $first_char = 'four';
          break;  
        case '5':
          $first_char = 'five';
          break;  
        case '6':
          $first_char = 'six';
          break;  
        case '7':
          $first_char = 'seven';
          break;  
        case '8':
          $first_char = 'eight';
          break;  
        case '9':
          $first_char = 'nine';
          break;   
        }
    }

    //first select the old value, subtract it by one, and update the value. Ideally this should be done in 1 update statement, but there seems to be no other way using
    //Mediawiki's database wrapper
    $dbr = wfGetDB(DB_SLAVE);
   
    $res = $dbr->select(
      'alphabetnumbers', //from
      array( //values
      $first_char,
      ),
      array(
      'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context),
      ),
      __METHOD__
    );
            
    //there should only be 1 result
    if ($res->numRows() === 1){
      $s = $res->fetchObject();
      $intvalue = (int)(($s->$first_char)-1);
      
      $dbw = wfGetDB(DB_MASTER);

      $dbw->update(
        'alphabetnumbers', //select table
        array( //insert values
        $first_char => $intvalue,
         ),
         array(
        'alphabetnumbers_context = ' . $dbw->addQuotes($alphabetnumbers_context),
      ),
       __METHOD__
    ); 
    
      if ($dbw->affectedRows()){
        return true;
      
      }else{
        return false;      
      }    
    }
    
    return false;      
  } 
  
  /**
   * This function deletes the entry for $page_title in the 'manuscripts' table
   */
  public function deleteDatabaseEntry($collection_name, $page_title_with_namespace){
    
    $dbw = wfGetDB(DB_MASTER);
        
    $dbw->delete( 
      'manuscripts', //from
      array( 
      'manuscripts_url' => $page_title_with_namespace), //conditions
      __METHOD__ );
    
    if ($dbw->affectedRows()){
      //something was deleted from the manuscripts table 

      if($collection_name !== null && $collection_name !== 'none'){
        //check if the collection has no pages left, and if so, delete the collection
        $this->checkAndDeleteCollection($collection_name);
      }

      return true;

    }else{
      //nothing was deleted
      return false;
    }
  }
  
  /**
   * This function checks if the collection is empty, and deletes the collection along with its metadata if this is the case
   */
  private function checkAndDeleteCollection($collection_name){
    
    $dbr = wfGetDB(DB_SLAVE);
    
    //First check if the collection is empty
    $res = $dbr->select(
        'manuscripts', //from
      array(
        'manuscripts_url',
      ),
      array(
        'manuscripts_collection = ' . $dbr->addQuotes($collection_name),
      ),
      __METHOD__
    );
        
    //If the collection is empty, delete the collection
    if($res->numRows() === 0){
          
      $dbw = wfGetDB(DB_MASTER);
    
      $dbw->delete( 
        'collections', //from
        array( 
        'collections_title' => $collection_name //conditions
          ), 
        __METHOD__ ); 
    }  
    
    return true; 
  }
  
  /**
   * This function retrieves the collection of the current page
   * 
   * @return type
   */
  public function getCollection($page_title_with_namespace = null){
        
    $dbr = wfGetDB(DB_SLAVE);
        
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_collection',//values
      ),
      array(
       'manuscripts_url = ' . $dbr->addQuotes($page_title_with_namespace),//conditions
      ), 
      __METHOD__,
      array(
      'ORDER BY' => 'manuscripts_collection',
      )
    );
        
    //there should only be 1 result
    if ($res->numRows() === 1){
      
      $collection_name = $res->fetchObject()->manuscripts_collection; 
      
      if(!empty($collection_name) && $collection_name !== 'none'){
        return htmlspecialchars($collection_name); 
      }
    }                
           
    return null; 
  }
}