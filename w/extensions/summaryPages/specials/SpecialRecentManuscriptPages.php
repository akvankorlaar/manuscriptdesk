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
      
    $summary_page_wrapper = new summaryPageWrapper('RecentManuscriptPages',$this->max_on_page);
    
    $title_array = $summary_page_wrapper->retrieveFromDatabase();
        
    $this->showPage($title_array);
  }
  
  /**
   * This function shows the page after a request has been processed
   * 
   * @param type $title_array
   */
  private function showPage($title_array){
    
    $out = $this->getOutput();
    $article_url = $this->article_url; 
        
    $out->setPageTitle($this->msg('recentmanuscriptpages'));
     
    if(empty($title_array)){
      return $out->addWikiText($this->msg('recentmanuscriptpages-nomanuscripts'));
    }
    
    $html = "";
    $html .= $this->msg('recentmanuscriptpages-information');
    
    $html .= "<table id='userpage-table' style='width: 100%;'>";
    $html .= "<tr>";
    $html .= "<td class='td-four'><b>" . $this->msg('userpage-tabletitle') . "</b></td>";
    $html .= "<td class='td-four'><b>" . $this->msg('userpage-user') . "</b></td>";
    $html .= "<td class='td-four'><b>" . $this->msg('userpage-collection') . "</b></td>";
    $html .= "<td class='td-four'><b>" . $this->msg('userpage-creationdate') . "</b></td>";
    $html .= "</tr>";
      
    foreach($title_array as $key=>$array){

      $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
      $user = isset($array['manuscripts_user']) ? $array['manuscripts_user'] : '';
      $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
      $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
      $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
        
      $html .= "<tr>";
      $html .= "<td class='td-four'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" . 
          htmlspecialchars($title) . "</a></td>";
      $html .= "<td class='td-four'>" . htmlspecialchars($user) . "</td>";
      $html .= "<td class='td-four'>" . htmlspecialchars($collection) . "</td>";
      $html .= "<td class='td-four'>" . htmlspecialchars($date) . "</td>";
      $html .= "</tr>";      
    }
      
    $html .= "</table>";
        
    return $out->addHTML($html);
  }
}


