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

class AllCollectionsWrapper extends ManuscriptDeskBaseWrapper{
           
  public function getData($button_name = '', $offset, $next_letter_alphabet, $max_on_page){
                
    $offset = isset($offset) ? $offset : 0;   
      
    $dbr = wfGetDB(DB_SLAVE);
    $collection_titles = array();
    $next_offset = null; 
    
    $res = $dbr->select(
        'collections', //from
      array(
        'collections_title',//values
        'collections_title_lowercase',
        'collections_user',
        'collections_date',        
        ), 
      array(
        'collections_title_lowercase >= ' . $dbr->addQuotes($button_name),
        'collections_title_lowercase < '  . $dbr->addQuotes($next_letter_alphabet),
        'collections_title_lowercase != ' . $dbr->addQuotes("none"),
       ),
      __METHOD__,
      array(
        'ORDER BY' => 'collections_title_lowercase',
        'LIMIT' => $max_on_page+1,
        'OFFSET' => $offset, 
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
        
        //add titles to the title array as long as it is not bigger than max_on_page
        if (count($collection_titles) < $max_on_page){
          
          $collection_titles[] = array(
          'collections_title' => $s->collections_title,
          'collections_user' => $s->collections_user,
          'collections_date' => $s->collections_date,
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $next_offset = ($offset)+($max_on_page);          
          break; 
        }
      }     
    }
   
    return array($collection_titles, $next_offset);   
  }
  
  /**
   * This function retrieves all the data for a single collection
   */
  public function retrieveSingleCollection($selected_collection = ''){
    
    $dbr = wfGetDB(DB_SLAVE);
    $meta_data = array();
    $pages_within_collection = array();
    
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
}