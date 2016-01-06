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

class SpecialAllCollations extends baseSummaryPage {
  
/**
 * SpecialallCollations page. Organises all collations 
 */
  
  public function __construct(){
    
    //call the parent constructor. The parent constructor (in 'summaryPages' class) will call the 'SpecialPage' class (grandparent) 
    parent::__construct('AllCollations');
  }
  
  /**
   * This function shows the page after a request has been processed
   * 
   * @param type $title_array
   */
  protected function showPage($title_array, $alphabet_numbers = array()){
      
//    for($i = 0; $i <= 38; $i++){
//      $alphabet_numbers[$i] = rand(0,1000);
//    }
    
    $out = $this->getOutput();    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollations'));
     
    $html ='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollations" method="post">';
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
    $html .= '</form>';
            
    if(empty($title_array)){
      
      $html .= $this->addSummaryPageLoader();
           
      if($this->is_number){
        $html .= "<p>" . $this->msg('allcollations-nocollations-number') . "</p>";
      }else{
        $html .= "<p>" . $this->msg('allcollations-nocollations') . "</p>"; 
      }
      
      return $out->addHTML($html);
    }
             
    if($this->previous_page_possible){
      
      $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
      $previous_message = $this->msg('allmanuscriptpages-previous');
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
      
      $html .='<form class="summarypage-form" id="previous-link" action="' . $article_url . 'Special:AllCollations" method="post">';
       
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' class='button-transparent' name = 'redirect_page_back' title='$previous_message_hover'  value='$previous_message'>";
      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_message_hover = $this->msg('allmanuscriptpages-nexthover');
      $next_message = $this->msg('allmanuscriptpages-next');
      
      $html .='<form class="summarypage-form" id="next-link" action="' . $article_url . 'Special:AllCollations" method="post">';
            
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>"; 
      $html .= "<input type='submit' class='button-transparent' name='redirect_page_forward' title='$next_message_hover' value='$next_message'>";
      
      $html.= "</form>";
    }
    
    $html .= $this->addSummaryPageLoader();
    
    $html .= "<table id='userpage-table' style='width: 100%;'>";
    $html .= "<tr>";
    $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
    $html .= "<td class='td-trhee'>" . "<b>" . $this->msg('userpage-user') . "</b>" . "</td>";
    $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-creationdate') . "</b>" . "</td>";
    $html .= "</tr>";
      
    foreach($title_array as $key=>$array){

      $title = isset($array['collations_main_title']) ? $array['collations_main_title'] : '';
      $url = isset($array['collations_url']) ? $array['collations_url'] : '';
      $user = isset($array['collations_user']) ? $array['collations_user'] : '';
      $date = isset($array['collations_date']) ? $array['collations_date'] : '';
        
      $html .= "<tr>";
      $html .= "<td class='td-three'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" . 
          htmlspecialchars($title) . "</a></td>";
      $html .= "<td class='td-three'>" . htmlspecialchars($user) . "</td>";
      $html .= "<td class='td-three'>" . htmlspecialchars($date) . "</td>";
      $html .= "</tr>";      
    }
      
    $html .= "</table>";
    
    return $out->addHTML($html);
  }
  
  /**
   * This function shows the default page if no request was posted 
   */
  protected function showDefaultPage($alphabet_numbers = array()){
      
    $out = $this->getOutput();   
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollations'));    
    
    $html ='<form class="summarypage-form-default" action="' . $article_url . 'Special:AllCollations" method="post">';    
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
    
    $html .= "<p>" . $this->msg('allcollations-instruction') . "</p>";
    
    return $out->addHTML($html);  
  }
}