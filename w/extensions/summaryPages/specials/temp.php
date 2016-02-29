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
  
  private $request_context; 
  private $max_on_page;
  private $offset; 
  private $user_name; 
  private $button_name; 
  private $next_page_possible; 
  private $next_letter_alphabet; 
  private $selected_collection;
  private $page_title; 
  
  //class constructor
  public function __construct($request_context, $max_on_page = 0, $offset = 0,$user_name = "", $button_name = "", $next_letter_alphabet = "", 
      $selected_collection = "", $page_title = ""){
    
    $this->request_context = $request_context;
    $this->max_on_page = $max_on_page;
    $this->offset = $offset;
    $this->user_name = $user_name; 
    $this->button_name = $button_name; 
    $this->next_page_possible = false; //default value
    $this->next_letter_alphabet = $next_letter_alphabet; 
    $this->selected_collection = $selected_collection;
    $this->page_title = $page_title; 
  }
  
  /**
   * This function decides which function to call, depending on the values given to the __constructor. 
   * 
   * The names of the functions are 'retrieve' + name of page that called this class
   * 
   * @return type
   */
  public function retrieveFromDatabase(){
    
    switch($this->request_context){
      
      case 'AllCollations':
        return $this->retrieveAllCollations();
        break;
      case 'AllCollections':
        return $this->retrieveAllCollections();
        break;
      case 'SingleManuscriptPages':
        return $this->retrieveSingleManuscriptPages();
        break; 
      case 'RecentManuscriptPages':
        return $this->retrieveRecentManuscriptPages();
        break;
      case 'viewmanuscripts':
        return $this->retrieveUserPageManuscriptPages();
        break;  
      case 'viewcollations':
        return $this->retrieveUserPageCollations();
        break;
      case 'viewcollections':
        return $this->retrieveUserPageCollections();
        break; 
      case 'singlecollection':
      case 'submitedit':
        return $this->retrieveSingleCollection();
        break; 
      case 'editmetadata':
      case 'getmetadata' :  
        return $this->retrieveMetadata();
        break;
    }
  }
   
  /**
   * This function retrieves all the data for a single collection
   */
  private function retrieveSingleCollection(){
    
    $selected_collection = $this->selected_collection; 
    $dbr = wfGetDB(DB_SLAVE);
    $meta_data = array();
    $pages_within_collection = array();
    
        //Database query
    $res = $dbr->select(
      'collections', //from
      array( //values
      'collections_metatitle',
      'collections_metaauthor',
      'collections_metayear' ,      
      'collections_metapages' ,    
      'collections_metacategory',   
      'collections_metaproduced',     
      'collections_metaproducer', 
      'collections_metaeditors',
      'collections_metajournal',
      'collections_metajournalnumber',
      'collections_metatranslators',  
      'collections_metawebsource',
      'collections_metaid',        
      'collections_metanotes',     
         ),
      array(
      'collections_title = ' . $dbr->addQuotes($selected_collection),
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'collections_title',
      )
      );
        
    //there should only be one result
    if ($res->numRows() === 1){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
                  
         $meta_data ['collections_metatitle']         = $s->collections_metatitle;
         $meta_data ['collections_metaauthor']        = $s->collections_metaauthor;
         $meta_data ['collections_metayear']          = $s->collections_metayear;
         $meta_data ['collections_metapages']         = $s->collections_metapages;
         $meta_data ['collections_metacategory']      = $s->collections_metacategory;
         $meta_data ['collections_metaproduced']      = $s->collections_metaproduced;
         $meta_data ['collections_metaproducer']      = $s->collections_metaproducer;
         $meta_data ['collections_metaeditors']       = $s->collections_metaeditors;
         $meta_data ['collections_metajournal']       = $s->collections_metajournal;
         $meta_data ['collections_metajournalnumber'] = $s->collections_metajournalnumber;
         $meta_data ['collections_metatranslators']   = $s->collections_metatranslators;
         $meta_data ['collections_metawebsource']     = $s->collections_metawebsource;
         $meta_data ['collections_metaid']            = $s->collections_metaid;
         $meta_data ['collections_metanotes']         = $s->collections_metanotes;       
      }     
    }
     
    //Database query
    $res = $dbr->select(
        'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_lowercase_title',
         ),
      array(
        'manuscripts_collection = ' . $dbr->addQuotes($selected_collection),
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
                  
        $pages_within_collection[] = array(
          'manuscripts_title' => $s->manuscripts_title,
          'manuscripts_url' => $s->manuscripts_url,
          'manuscripts_date' => $s->manuscripts_date,  
        );      
      }     
    }
    
    return array($meta_data, $pages_within_collection);
  }
  
  /**
   * 
   */
  private function retrieveMetadata(){
    
    $user_name = $this->user_name;
    $selected_collection = $this->selected_collection; 
    $dbr = wfGetDB(DB_SLAVE);
    $meta_data = array();
    
    if(!empty($user_name)){
      $conditions = array(
      'collections_user = ' . $dbr->addQuotes($user_name),
      'collections_title = ' . $dbr->addQuotes($selected_collection),
      );  
      
    }else{
      $conditions = array(
      'collections_title = ' . $dbr->addQuotes($selected_collection),
      );   
    }
    
    //Database query
    $res = $dbr->select(
      'collections', //from
      array( //values
      'collections_metatitle',
      'collections_metaauthor',
      'collections_metayear' ,      
      'collections_metapages' ,    
      'collections_metacategory',   
      'collections_metaproduced',     
      'collections_metaproducer', 
      'collections_metaeditors',
      'collections_metajournal',
      'collections_metajournalnumber',
      'collections_metatranslators',  
      'collections_metawebsource',
      'collections_metaid',        
      'collections_metanotes',     
         ),
        $conditions,
      __METHOD__,
      array(
        'ORDER BY' => 'collections_title',
      )
      );
        
    //there should only be one result
    if ($res->numRows() === 1){
      $s = $res->fetchObject();
                  
      $meta_data ['collections_metatitle']         = $s->collections_metatitle;
      $meta_data ['collections_metaauthor']        = $s->collections_metaauthor;
      $meta_data ['collections_metayear']          = $s->collections_metayear;
      $meta_data ['collections_metapages']         = $s->collections_metapages;
      $meta_data ['collections_metacategory']      = $s->collections_metacategory;
      $meta_data ['collections_metaproduced']      = $s->collections_metaproduced;
      $meta_data ['collections_metaproducer']      = $s->collections_metaproducer;
      $meta_data ['collections_metaeditors']       = $s->collections_metaeditors;
      $meta_data ['collections_metajournal']       = $s->collections_metajournal;
      $meta_data ['collections_metajournalnumber'] = $s->collections_metajournalnumber;
      $meta_data ['collections_metatranslators']   = $s->collections_metatranslators;
      $meta_data ['collections_metawebsource']     = $s->collections_metawebsource;
      $meta_data ['collections_metaid']            = $s->collections_metaid;
      $meta_data ['collections_metanotes']         = $s->collections_metanotes;           
    }
    
    return $meta_data; 
  }
  
  /**
   * This function inserts data into the 'collections' table 
   */
  public function insertCollections($form_data){
        
    $user_name = $this->user_name;
    $selected_collection = $this->selected_collection;
    
    $metatitle =         isset($form_data['wptextfield1']) ? $form_data['wptextfield1'] : '';
    $metaauthor =        isset($form_data['wptextfield2']) ? $form_data['wptextfield2'] : '';
    $metayear =          isset($form_data['wptextfield3']) ? $form_data['wptextfield3'] : '';
    $metapages =         isset($form_data['wptextfield4']) ? $form_data['wptextfield4'] : '';
    $metacategory =      isset($form_data['wptextfield5']) ? $form_data['wptextfield6'] : '';
    $metaproduced =      isset($form_data['wptextfield6']) ? $form_data['wptextfield8'] : '';
    $metaproducer =      isset($form_data['wptextfield7']) ? $form_data['wptextfield9'] : '';
    $metaeditors =       isset($form_data['wptextfield8']) ? $form_data['wptextfield8'] : '';
    $metajournal =       isset($form_data['wptextfield9']) ? $form_data['wptextfield9'] : '';
    $metajournalnumber = isset($form_data['wptextfield10']) ? $form_data['wptextfield10'] : '';
    $metatranslators =   isset($form_data['wptextfield11']) ? $form_data['wptextfield11'] : '';
    $metawebsource =     isset($form_data['wptextfield12']) ? $form_data['wptextfield12'] : '';
    $metaid =            isset($form_data['wptextfield13']) ? $form_data['wptextfield13'] : '';
    $metanotes =         isset($form_data['wptextfield14']) ? $form_data['wptextfield14'] : '';
    
    $dbw = wfGetDB(DB_MASTER);
    
    $dbw->update('collections', //select table
      array( //update values
      'collections_metatitle'         => $metatitle,
      'collections_metaauthor'        => $metaauthor,
      'collections_metayear'          => $metayear,
      'collections_metapages'         => $metapages,
      'collections_metacategory'      => $metacategory,
      'collections_metaproduced'      => $metaproduced,  
      'collections_metaproducer'      => $metaproducer,
      'collections_metaeditors'       => $metaeditors,
      'collections_metajournal'       => $metajournal,
      'collections_metajournalnumber' => $metajournalnumber,
      'collections_metatranslators'   => $metatranslators,
      'collections_metawebsource'     => $metawebsource,   
      'collections_metaid'            => $metaid,
      'collections_metanotes'         => $metanotes,
       ),
        array(
      'collections_user  = ' . $dbw->addQuotes($user_name),//conditions
      'collections_title = ' . $dbw->addQuotes($selected_collection),
        ), //conditions
        __METHOD__,
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
   * This function retrieves the page id from the 'page' table 
   */
  public function retrievePageId(){
    
    $page_id = false; 
    $page_title = $this->page_title; 
    $dbr = wfGetDB(DB_SLAVE);
    
     //Database query
    $res = $dbr->select(
        'page', //from
      array(
        'page_id',//values
         ),
      array(
        'page_namespace = ' . $dbr->addQuotes(NS_MANUSCRIPTS),
        'page_title = ' . $dbr->addQuotes($page_title),
      ),
      __METHOD__,
      array(
        'ORDER BY' => 'page_id', //is this needed? 
      )
      );
        
    //there should only be one result
    if ($res->numRows() === 1){
      $page_id = $res->fetchObject()->page_id;    
    }
    
    return $page_id; 
  }
    
}