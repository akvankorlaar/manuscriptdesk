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

class SpecialAllCollections extends baseSummaryPage {
  
/**
 * SpecialallCollections page. Organises all collections. The method 'execute', located in the parent class 'summaryPages', is the first class that will run when opening
 * this page.  
 */
  
  public function __construct(){
    
    //call the parent constructor. The parent constructor (in 'summaryPages' class) will call the 'SpecialPage' class (grandparent) 
    parent::__construct('AllCollections');
  }
        
  /**
   * This function shows the page after a request has been processed
   * 
   * @param type $title_array
   */
  protected function showPage($title_array, $alphabet_numbers = array()){
    
    $out = $this->getOutput();  
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollections'));
    
    $html ='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollections" method="post">';
    $html .= "<table>";

    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 
    
    $a = 0;
    
    foreach($uppercase_alphabet as $key=>$value){
       
      if($a === 0){
        $html .= "<tr>";    
      }
        
      if($a === (count($uppercase_alphabet)/2)){
        $html .= "</tr>";
        $html .= "<tr>";  
      }
      
      $name = $lowercase_alphabet[$key];
      
      if(isset($alphabet_numbers[$key]) && $alphabet_numbers[$key] > 0){
        $alphabet_number = $alphabet_numbers[$key];  
      }else{
        $alphabet_number = '';  
      }    
      
      if($this->button_name === $name){   
        $html .= "<td>";
        $html .= "<div class='letter-div-active' style='display:inline-block;'>";
        $html .= "<input type='submit' name='$name' class='letter-button-active' value='$value'>";
        $html .= "<small>$alphabet_number</small>";
        $html .= "</div>";  
        $html .= "</td>";
      }else{
        $html .= "<td>";
        $html .= "<div class='letter-div-initial' style='display:inline-block;'>";
        $html .= "<input type='submit' name='$name' class='letter-button-initial' value='$value'>";
        $html .= "<small>$alphabet_number</small>";
        $html .= "</div>";  
        $html .= "</td>";    
      }
      
      $a+=1; 
    } 
    
    $html .= "</tr>";
    $html .= "</table>";
    $html .= '</form>';
                
    if(empty($title_array)){
      
      $html .= $this->addSummaryPageLoader();
           
      if($this->is_number){
        $html .= "<p>" . $this->msg('allcollections-nocollections-number') . "</p>";
      }else{
        $html .= "<p>" . $this->msg('allcollections-nocollections') . "</p>"; 
      }
      
      return $out->addHTML($html);
    }
    
    if($this->previous_page_possible){
      
      $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
      $previous_message = $this->msg('allmanuscriptpages-previous');
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
         
      $html .='<form class="summarypage-form" id="previous-link" action="' . $article_url . 'Special:AllCollections" method="post">';
       
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' class='button-transparent' name='redirect_page_back' title='$previous_message_hover'  value='$previous_message'>";
      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_message_hover = $this->msg('allmanuscriptpages-nexthover');    
      $next_message = $this->msg('allmanuscriptpages-next');
      
      $html .='<form class="summarypage-form" id="next-link" action="' . $article_url . 'Special:AllCollections" method="post">';
            
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>"; 
      $html .= "<input type='submit' class='button-transparent' name = 'redirect_page_forward' title='$next_message_hover' value='$next_message'>";
      
      $html.= "</form>";
    }
    
    $html .= $this->addSummaryPageLoader();
            
    $html .= "<form id='allcollections-post' action='" . $article_url . "Special:AllCollections' method='post'>";
    $html .= "<table id='userpage-table' style='width: 100%;'>";
    $html .= "<tr>";
    $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-collection') . "</b>" . "</td>";
    $html .= "<td class='td-trhee'>" . "<b>" . $this->msg('userpage-user') . "</b>" . "</td>";
    $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
    $html .= "</tr>";
      
    foreach($title_array as $key=>$array){

      $title = isset($array['collections_title']) ? $array['collections_title'] : '';
      $user = isset($array['collections_user']) ? $array['collections_user'] : '';
      $date = isset($array['collections_date']) ? $array['collections_date'] : '';
        
      $html .= "<tr>";
      $html .= "<td class='td-three'>";
      $html .= "<input type='submit' class='button-transparent' name='singlecollection' value='" . htmlspecialchars($title) . "'>";
      $html .= "</td>";
      $html .= "<td class='td-three'>" . htmlspecialchars($user) . "</td>";
      $html .= "<td class='td-three'>" . htmlspecialchars($date) . "</td>";
      $html .= "</tr>";      
    }
      
    $html .= "</table>";
    $html .= "</form>";
        
    //this has to be added explicitly and not in the hook because somehow mPrefixedText does not work in this case
    $out->addModuleStyles('ext.userPage');
    
    return $out->addHTML($html);  
  }
  
  /**
   * This function shows single collection data
   *  
   * @param type $single_collection_data
   */
  protected function showSingleCollectionData($single_collection_data, $alphabet_numbers = array()){
    
    $out = $this->getOutput();   
    $article_url = $this->article_url;
    $selected_collection = $this->selected_collection;
    list($meta_data, $pages_within_collection) = $single_collection_data; 
        
    $out->setPageTitle($this->msg('allcollections'));    
    
    $html ='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollections" method="post">';
    $html .= "<table>";

    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 
    
    $a = 0;
    
    foreach($uppercase_alphabet as $key=>$value){
       
      if($a === 0){
        $html .= "<tr>";    
      }
        
      if($a === (count($uppercase_alphabet)/2)){
        $html .= "</tr>";
        $html .= "<tr>";  
      }
      
      $name = $lowercase_alphabet[$key];
      $alphabet_number = isset($alphabet_numbers[$key]) ? $alphabet_numbers[$key] : '';
      
      if($this->button_name === $name){   
        $html .= "<td>";
        $html .= "<div class='letter-div-active' style='display:inline-block;'>";
        $html .= "<input type='submit' name='$name' class='letter-button-active' value='$value'>";
        $html .= "<small>$alphabet_number</small>";
        $html .= "</div>";  
        $html .= "</td>";
      }else{
        $html .= "<td>";
        $html .= "<div class='letter-div-initial' style='display:inline-block;'>";
        $html .= "<input type='submit' name='$name' class='letter-button-initial' value='$value'>";
        $html .= "<small>$alphabet_number</small>";
        $html .= "</div>";  
        $html .= "</td>";    
      }
      
      $a+=1; 
    } 
    
    $html .= "</tr>";
    $html .= "</table>";
    $html .= '</form><br>';
      
    $html .= $this->addSummaryPageLoader();
    
    $html .= "<div id='userpage-singlecollectionwrap'>"; 
        
    $html .= "<h2 style='text-align: center;'>" . $this->msg('userpage-collection') . ": " . $selected_collection . "</h2>";
    $html .= "<br>";    
    $html .= "<h3>" . $this->msg('userpage-metadata') . "</h3>";
    
    $collection_meta_table = new collectionMetaTable(); 
    
    $html .= $collection_meta_table->renderTable($meta_data);

    $html .= "<h3>Pages</h3>"; 
    $html .= $this->msg('userpage-contains') . " " . count($pages_within_collection) . " " . $this->msg('userpage-contains2');
    $html .= "<br>";
    
    $html .= "<table id='userpage-table' style='width: 100%;'>";
    $html .= "<tr>";
    $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
    $html .= "<td>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
    $html .= "</tr>";
    
    foreach($pages_within_collection as $key=>$array){

      $manuscripts_url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
      $manuscripts_title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : ''; 
      $manuscripts_date = isset($array['manuscripts_date']) ? $array['manuscripts_date'] : '';
      
      $html .= "<tr>";
      $html .= "<td class='td-long'><a href='" . $article_url . htmlspecialchars($manuscripts_url) . "' title='" . htmlspecialchars($manuscripts_url) . "'>" . 
          htmlspecialchars($manuscripts_title) . "</a></td>";
      $html .= "<td>" . htmlspecialchars($manuscripts_date) . "</td>";
      $html .= "</tr>";
    }
    
    $html .= "</table>";
    $html .= "</div>";
    
    $html .= "</div>";
      
    return $out->addHTML($html);
  }
    
  /**
   * This function shows the default page if no request was posted 
   */
  protected function showDefaultPage($alphabet_numbers = array()){
      
    $out = $this->getOutput();   
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollections'));    
    
    $html ='<form class="summarypage-form-default" action="' . $article_url . 'Special:AllCollections" method="post">';    
    $html .= "<table>"; 
    
    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet;
    $a = 0; 
            
    foreach($uppercase_alphabet as $key=>$value){
       
      if($a === 0){
        $html .= "<tr>";    
      }
        
      if($a === (count($uppercase_alphabet)/2)){
        $html .= "</tr>";
        $html .= "<tr>";  
      }
      
      $name = $lowercase_alphabet[$key];
      
      if(isset($alphabet_numbers[$key]) && $alphabet_numbers[$key] > 0){
        $alphabet_number = $alphabet_numbers[$key];  
      }else{
        $alphabet_number = '';  
      }
      
      $html .= "<td>";
      $html .= "<div class='letter-div-initial' style='display:inline-block;'>";
      $html .= "<input type='submit' name='$name' class='letter-button-initial' value='$value'>";
      $html .= "<small>$alphabet_number</small>";
      $html .= "</div>";  
      $html .= "</td>";
      
      $a+=1; 
    } 
    
    $html .= "</tr>";
    $html .= "</table>"; 
    $html .= '</form><br>';
     
    $html .= $this->addSummaryPageLoader();
    
    $html .= "<p>" . $this->msg('allcollections-instruction') . "</p>";
    
    return $out->addHTML($html);  
  }
}