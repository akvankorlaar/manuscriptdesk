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
}