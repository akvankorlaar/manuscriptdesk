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
   * This function prepares the database configuration settings, and then calls the database to fetch manuscript titles
   * 
   * @return type an array of all manuscripts
   */
  protected function retrieveManuscriptTitles(){
            
    $dbr = wfGetDB(DB_SLAVE);
    
    $button_name = $this->button_name;    
    $next_letter_alphabet = $this->getNextLetter();
                           
    $conds = array(
    'collations_main_title_lowercase >= ' . $dbr->addQuotes($button_name),
    'collations_main_title_lowercase < ' . $dbr->addQuotes($next_letter_alphabet), 
    );
             
    return $this->retrieveFromDatabase($dbr,$conds);    
  }
  
  /**
   * This function retrieves titles from the wiki database
   * 
   * @return type
   */
  protected function retrieveFromDatabase($dbr,$conds, $title_array = array()){
    
    //Database query
    $res = $dbr->select(
      'collations', //from
      array(
        'collations_user',//values
        'collations_url',
        'collations_date',
        'collations_main_title',
        'collations_main_title_lowercase'
        ), 
      $conds, //conditions
      __METHOD__,
      array(
        'ORDER BY' => 'collations_main_title_lowercase',
        'LIMIT' => $this->max_on_page+1,
        'OFFSET' => $this->offset, 
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
        
        //add titles to the title array as long as it is not bigger than max_on_page
        if (count($title_array) < $this->max_on_page){
          
          $title_array[] = array(
          'collations_user'       => $s->collations_user,
          'collations_url'        => $s->collations_url,
          'collations_date'       => $s->collations_date,
          'collations_main_title' => $s->collations_main_title,
        );

        //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
        }else{
          $this->next_page_possible = true;
          $this->next_offset = ($this->offset)+($this->max_on_page);          
          break; 
        }
      }     
    }
   
    return $title_array;   
  }
    
  /**
   * This function shows the page after a request has been processed
   * 
   * @param type $title_array
   */
  protected function showPage($title_array){
    
    $out = $this->getOutput(); 
    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollations-title'));
     
    $html ='<form action="' . $article_url . 'Special:AllCollations" method="post">';

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
     
      $out->addHTML($html);
      
      if($this->is_number){
        return $out->addWikiText($this->msg('allcollations-nocollations-number'));
      }

      return $out->addWikiText($this->msg('allcollations-nocollations'));
    }
             
    if($this->previous_page_possible){
      
      $previous_hover_message = $this->msg('allcollations-previoushover');
      $previous_message = $this->msg('allcollations-previous');
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
      
      $html .='<form action="' . $article_url . 'Special:AllCollations" method="post">';
       
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' name = 'redirect_page_back' id='button' title='$previous_hover_message'  value='$previous_message'>";
      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_hover_message = $this->msg('allcollations-nexthover');
      $next_message = $this->msg('allcollations-next');
      
      $html .='<form action="' . $article_url . 'Special:AllCollations" method="post">';
            
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .=("<input type='hidden' name='$this->button_name' value='$this->button_name'>"); 
      $html .= "<input type='submit' name = 'redirect_page_forward' id='button' title='$next_hover_message' value='$next_message'>";
      
      $html.= "</form>";
    }
        
    $out->addHTML($html);
    
    $created_message = $this->msg('allcollations-created');  
    $on_message = $this->msg('allcollations-on');
    
    foreach($title_array as $key=>$array){
      
      $user = isset($array['collations_user']) ? $array['collations_user'] : ''; 
      $url = isset($array['collations_url']) ? $array['collations_url'] : '';
      $date = isset($array['collations_date']) ? $array['collations_date'] : '';
      $title = isset($array['collations_main_title']) ? $array['collations_main_title'] : '';
      
      $out->addWikiText('[[' . $url . '|' . $title . ']]<br>' . $created_message . ' ' . $user . '<br> ' . $on_message . ' ' . $date);  
    }
    
    return true; 
  }
  
  /**
   * This function shows the default page if no request was posted 
   */
  protected function showDefaultPage(){
      
    $out = $this->getOutput();
    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('allcollations-title'));    
    
    $html ='<form action="' . $article_url . 'Special:AllCollations" method="post">';

    //make a list of buttons that have as value a letter of the alphabet
    $uppercase_alphabet = $this->uppercase_alphabet;  
    $lowercase_alphabet = $this->lowercase_alphabet; 
    
    foreach($uppercase_alphabet as $key=>$value){
      $name = $lowercase_alphabet[$key]; 
      $html .="<input type='submit' name='$name' id='initial_button' value='$value'>";
    } 
    
    $html .= '</form><br>';
    
    $out->addHTML($html);  
    
    return $out->addWikiText($this->msg('allcollations-instruction'));
  }
}

