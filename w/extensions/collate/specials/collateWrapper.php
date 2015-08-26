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

class collateWrapper{
  
  private $user_name; 
  private $maximum_manuscripts; 

  //class constructor
  public function __construct($user_name = "", $maximum_manuscripts = null){
    
    $this->user_name = $user_name;
    $this->maximum_manuscripts = $maximum_manuscripts; 
  }
  
  /**
   * This function checks if any uploaded manuscripts are part of a larger collection of manuscripts by retrieving data from the 'manuscripts' table
   * 
   * @param type $collection_urls
   * @return type
   */
  public function checkForManuscriptCollections(){
    
    $user_name = $this->user_name; 
    $dbr = wfGetDB(DB_SLAVE);
    $collection_urls = array();
        
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_collection',
        'manuscripts_lowercase_title',
      ),
      array(
       'manuscripts_user = ' . $dbr->addQuotes($user_name),//conditions
       'manuscripts_collection != ' . $dbr->addQuotes("none"),
     ),
      __METHOD__,
      array(
      'ORDER BY' => 'manuscripts_lowercase_title',
      )
    );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
                    
        //check if the current collection has been added
        if(!isset($collection_urls[$s->manuscripts_collection])){
          $collection_urls[$s->manuscripts_collection] = array(
              'manuscripts_url' => array($s->manuscripts_url),
              'manuscripts_title' => array($s->manuscripts_title),
              );
     
        //if the collection already has been added, append the new manuscripts_url to the current array
        }else{
          end($collection_urls);
          $key = key($collection_urls);
          $collection_urls[$key]['manuscripts_url'][] = $s->manuscripts_url;
          $collection_urls[$key]['manuscripts_title'][] = $s->manuscripts_title;

        }                
      }     
    }
    
    //remove collections that have contain too many pages
    foreach ($collection_urls as $collection_name => &$small_url_array){
      if (count($small_url_array['manuscripts_url']) > ($this->maximum_manuscripts-1)){
        unset($collection_urls[$collection_name]);
      }
    }
  
    return $collection_urls; 
  }
  
  /**
   * This function fetches the data showing which pages have been created by the current user by retrieving this data from the 'manuscripts' table
   * 
   * @return type
   */
  public function getManuscriptTitles(){
    
    $dbr = wfGetDB(DB_SLAVE);
    $user_name = $this->user_name;
    $url_array = array();
    $title_array = array();
        
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_lowercase_title',
         ),
      array(
      'manuscripts_user = ' . $dbr->addQuotes($user_name), //conditions: the user should be the current user
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
        
        //add titles to the title array and url array     
        $title_array[] = $s->manuscripts_title;
        $url_array[] = $s->manuscripts_url;     
      }     
    }
   
  return array($url_array, $title_array);   
  }
  
  /**
   * This function gets the stored collate values from 'tempcollate'
   */
  public function getTempcollate(){
        
    $dbr = wfGetDB(DB_SLAVE);
    $user_name = $this->user_name; 
        
    //Database query
    $res = $dbr->select(
      'tempcollate', //from
      array(
        'tempcollate_user',//values
        'tempcollate_titles_array',
        'tempcollate_new_url',
        'tempcollate_main_title',
        'tempcollate_main_title_lowercase',
        'tempcollate_collatex'
         ),
       array(
      'tempcollate_user = ' . $dbr->addQuotes($user_name), //conditions
      ),
      __METHOD__ 
      );
        
    if ($res->numRows() === 1){
      $s = $res->fetchObject();
       
      $titles_array = $s->tempcollate_titles_array;
      $new_url = $s->tempcollate_new_url;
      $main_title = $s->tempcollate_main_title;
      $main_title_lowercase = $s->tempcollate_main_title_lowercase; 
      $collatex_output = $s->tempcollate_collatex;
          
      return array($titles_array, $new_url, $main_title, $main_title_lowercase, $collatex_output);
    
    }else{

      return false; 
    }
  }
  
  /**
   * Insert the result of the collation into 'tempcollate', which will be used when the user wants to save the current table
   *  
   * @param type $collatex_output
   */
  public function storeTempcollate($titles_array, $main_title, $new_url, $collatex_output){
        
    $titles_array = json_encode($titles_array);
    $main_title_lowercase = strtolower($main_title);

		$dbw = wfGetDB(DB_MASTER);
    
    $insert_values = array(
      'tempcollate_user'                  => $this->user_name,  
      'tempcollate_titles_array'          => $titles_array,
      'tempcollate_new_url'               => $new_url,
      'tempcollate_main_title'            => $main_title,
      'tempcollate_main_title_lowercase'  => $main_title_lowercase,
      'tempcollate_collatex'              => $collatex_output
			);
    
    //upsert = INSERT.. ON DUPLICATE KEY UPDATE
    $dbw->upsert(
        'tempcollate', //select table
         $insert_values,
         array('tempcollate_user'), //tempcollate_unique
         $insert_values,
         __METHOD__ 
        );
    
    if ($dbw->affectedRows()){
      //insert succeeded
      return true;
    
    }else{
      //return error    
      return false;
		}
  }
  
  /**
   * This function stores the collation data in 'collations' when the user chooses to save the current table
   * 
   * @param type $new_url
   * @param type $main_title
   * @param type $main_title_lowercase
   * @return boolean
   */
  public function storeCollations($new_url, $main_title, $main_title_lowercase, $titles_array, $collatex_output){
      
    $user_name = $this->user_name; 
      
    $date = date("d-m-Y H:i:s"); 
    
    $main_title_lowercase = strtolower($main_title);
    
    $dbw = wfGetDB(DB_MASTER);
    $dbw->insert('collations', //select table
      array( //insert values
      'collations_user'                 => $user_name,
      'collations_url'                  => $new_url,
      'collations_date'                 => $date,
      'collations_main_title'           => $main_title, 
      'collations_main_title_lowercase' => $main_title_lowercase,
      'collations_titles_array'         => $titles_array,
      'collations_collatex'             => $collatex_output
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
}