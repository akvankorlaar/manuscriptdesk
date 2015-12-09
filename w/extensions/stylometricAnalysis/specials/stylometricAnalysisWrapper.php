<?php
/**
 * This file is part of the collate extension
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

class stylometricAnalysisWrapper{
  
  private $user_name; 
  private $minimum_pages_per_collection;
  private $hours_before_delete; 

  //class constructor
  public function __construct($user_name, $minimum_pages_per_collection = 0){
    
    global $wgStylometricAnalysisOptions; 
    
    $this->user_name = $user_name;
    $this->minimum_pages_per_collection = $minimum_pages_per_collection;
    $this->hours_before_delete = $wgStylometricAnalysisOptions['tempstylometricanalysis_hours_before_delete']; 
  }
  /**
   * This function checks if any uploaded manuscripts are part of a larger collection of manuscripts by retrieving data from the 'manuscripts' table
   * 
   * @param type $collection_urls
   * @return type
   */
  public function checkForManuscriptCollections(){
    
    $user_name = $this->user_name;
    $minimum_pages_per_collection = $this->minimum_pages_per_collection; 
    $dbr = wfGetDB(DB_SLAVE);
    $collection_urls = array();       
    
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_collection',
      ),
      array(
        'manuscripts_user = ' . $dbr->addQuotes($user_name), //conditions
        'manuscripts_collection != ' . $dbr->addQuotes("none"),
       ),
      __METHOD__,
      array(
      'ORDER BY' => 'manuscripts_lowercase_collection',
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
    
    //remove collections with less pages than $this->minimum_pages_per_collection from the list
    foreach($collection_urls as $collection_name => &$small_url_array){
      if(count($small_url_array['manuscripts_url']) < $minimum_pages_per_collection){
        unset($collection_urls[$collection_name]);
      }
    }
      
    return $collection_urls;  
  }
  
  /**
   * This function checks if values should be removed from the 'tempstylometricanalysis' table
   */
  public function clearOldValues($current_time){
        
    $dbr = wfGetDB(DB_SLAVE);
    $user_name = $this->user_name; 
    $time_array = array();
        
    //Database query
    $res = $dbr->select(
        'tempstylometricanalysis', //from
      array(
        'tempstylometricanalysis_user',//values
        'tempstylometricanalysis_time',
        'tempstylometricanalysis_fulloutputpath1',
        'tempstylometricanalysis_fulloutputpath2',
         ),
      array(
        'tempstylometricanalysis_user = ' . $dbr->addQuotes($user_name), //conditions
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'tempstylometricanalysis_time',
      )
      );
      
    //while there are still titles in this query
    while ($s = $res->fetchObject()){
       
      $time_array[] = $s->tempstylometricanalysis_time;    
    }
    
    foreach($time_array as $index=>$time){
      
      if($current_time - $time > ($this->hours_before_delete * 3600)){  
        
        //delete images
        
        
        $status = $this->deleteTempStylometricAnalysis($time);
        
        //deletion of an element failed, so something went wrong
        if(!$status){
          return false; 
        }
      }
    }
    
    return true;    
  }
  
  /**
   * This function removes values from the 'tempstylometricanalysis' table
   * 
   * @param type $time
   */
  private function deleteTempStylometricAnalysis($time){
     
    $dbw = wfGetDB(DB_MASTER);    
    $user_name = $this->user_name; 
    
    $dbw->delete( 
      'tempstylometricanalysis', //from
      array( 
      'tempstylometricanalysis_user = ' . $dbw->addQuotes($user_name), //conditions
      'tempstylometricanalysis_time = ' . $dbw->addQuotes($time),
        ),
      __METHOD__ );
    
    if ($dbw->affectedRows()){
      //something was deleted from the tempstylometricanalysis table  
      return true;
    }else{
      //nothing was deleted
      return false;
    }  
  }
}