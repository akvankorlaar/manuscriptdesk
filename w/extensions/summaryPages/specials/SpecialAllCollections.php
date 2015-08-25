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
  protected function showPage($title_array){
    
    $out = $this->getOutput(); 
    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollections'));
    
    $html ='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollections" method="post">';

    //make a list of buttons that have as value a letter of the alphabet
    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 

    foreach($uppercase_alphabet as $key=>$value){
      $name = $lowercase_alphabet[$key]; 

      if($this->button_name === $name){
        $html .= "<input type='submit' name='$name' id='active_button' value='$value'>";
      }else{
        $html .= "<input type='submit' name='$name' id='letter_button' value='$value'>";
      }
    }

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
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
      
      $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
      $previous_message = $this->msg('allmanuscriptpages-previous');
      
      $html .='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollections" method="post">';
       
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' id = 'button' name = 'redirect_page_back' id='button' title='$previous_message_hover'  value='$previous_message'>";
      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_message_hover = $this->msg('allmanuscriptpages-nexthover');    
      $next_message = $this->msg('allmanuscriptpages-next');
      
      $html .='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollections" method="post">';
            
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>"; 
      $html .= "<input type='submit' id = 'button' name = 'redirect_page_forward' id='button' title='$next_message_hover' value='$next_message'>";
      
      $html.= "</form>";
    }
    
    $html .= $this->addSummaryPageLoader();
        
    $out->addHTML($html);
    
    $created_message = $this->msg('allmanuscriptpages-created');
    $on_message = $this->msg('allmanuscriptpages-on');
    
    $displayed_collections = array();
    $wiki_text = "";
    
    foreach($title_array as $key=>$array){
      
      $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
      $user = isset($array['manuscripts_user']) ? $array['manuscripts_user'] : '';
      $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
      $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
      $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
      
      if(in_array($collection, $displayed_collections)){
          $wiki_text .= '<br><br>[[' . $url . '|' . $title .']] <br>' . $created_message . ' ' . $user .  '<br> ' . $on_message . $date;
          
      }else{
          $wiki_text .= '<br><br>' . "'''" . $collection . ':' . "'''" . '<br><br>' . '[[' . $url . '|' . $title .']] <br>' . $created_message . ' ' . $user .  '<br> ' . $on_message . $date;
          $displayed_collections[] = $collection; 
      }             
    }
    
    $out->addWikiText($wiki_text);      
    
    return true; 
  }
  
  /**
   * This function shows the default page if no request was posted 
   */
  protected function showDefaultPage(){
      
    $out = $this->getOutput();
    
    $article_url = $this->article_url; 
        
    $out->setPageTitle($this->msg('allcollections'));    
    
    $html ='<form class="summarypage-form" action="' . $article_url . 'Special:AllCollections" method="post">';

    //make a list of buttons that have as value a letter of the alphabet
    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 
    
    foreach($uppercase_alphabet as $key=>$value){
      $name = $lowercase_alphabet[$key]; 
      $html .="<input type='submit' name='$name' id='initial_button' value='$value'>";
    } 
    
    $html .= '</form><br>';
    
    $html .= $this->addSummaryPageLoader();
    
    $html .= "<p>" . $this->msg('allcollections-instruction') . "</p>";
    
    return $out->addHTML($html);
  }
}


