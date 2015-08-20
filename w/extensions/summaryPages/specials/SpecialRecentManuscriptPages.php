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

class SpecialRecentManuscriptPages extends SpecialPage {
  
/**
 * Specialrecentmanuscriptpages page. Organises the most recently added manuscript pages 
 */
  
  public $article_url;
  
  private $max_on_page; //maximum manuscripts shown on a page
  
   //class constructor
  public function __construct(){
    
    global $wgNewManuscriptOptions, $wgArticleUrl; 
    
    $this->article_url = $wgArticleUrl; 
    
    $this->max_on_page = $wgNewManuscriptOptions['max_recent'];
    
    parent::__construct('RecentManuscriptPages');
  }
  
  /**
   * This function 
   */
  public function execute(){
        
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
                             
    $conds = array(
    'manuscripts_lowercase_title >= ' . $dbr->addQuotes(""),
    );
             
    return $this->retrieveFromDatabase($dbr,$conds);    
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
        'manuscripts_lowercase_title',
        'manuscripts_datesort'
        ), 
      $conds, //conditions
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
   * This function shows the page after a request has been processed
   * 
   * @param type $title_array
   */
  private function showPage($title_array){
    
    $out = $this->getOutput(); 
        
    $out->setPageTitle($this->msg('recentmanuscriptpages-title'));
     
    if(empty($title_array)){
      return $out->addWikiText($this->msg('recentmanuscriptpages-nomanuscripts'));
    }
            
    $created_message = $this->msg('allmanuscriptpages-created');
    $on_message = $this->msg('allmanuscriptpages-on');
    
    $wiki_text = "";
    
    $wiki_text .= ($this->msg('recentmanuscriptpages-information'));
    
    foreach($title_array as $key=>$array){
      
      $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
      $user = isset($array['manuscripts_user']) ? $array['manuscripts_user'] : '';
      $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
      $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
      $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
                  
      $wiki_text .= '<br><br>[[' . $url . '|' . $title . ']] (' . $collection . ')<br>' . $created_message . ' ' . $user .  '<br> ' . $on_message . ' ' . $date;      
    }
        
    return $out->addWikiText($wiki_text);
  }
}


