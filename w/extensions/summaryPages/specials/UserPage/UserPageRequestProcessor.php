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

class UserPageRequestProcessor extends ManuscriptDeskBaseRequestProcessor{
    
      /**
   * This function loads requests when a user selects a button, moves to the previous page, or to the next page
   */
  private function loadRequest(){
    
    $request = $this->getRequest();
        
    if(!$request->wasPosted()){
      return false;  
    }
    
    $posted_names = $request->getValueNames();    
     
    //identify the button pressed, and assign $posted_names to values
    foreach($posted_names as $key=>$original_value){
      
      $value = trim(str_replace(range(0,9),'',$original_value));
      //get the posted button      
      if($value === 'view_manuscripts_posted'){
        $this->view_manuscripts = true; 
        $this->id_manuscripts = 'button-active';
        $this->button_name = $value; 
        
      }elseif($value === 'view_collations_posted'){
        $this->view_collations = true; 
        $this->id_collations = 'button-active';
        $this->button_name = $value;   
        
      }elseif($value === 'view_collections_posted'){
        $this->view_collections = true; 
        $this->id_collections = 'button-active';
        $this->button_name = $value;
        
      }elseif($value === 'wpEditToken'){
        $token = $request->getText($value);
        $this->token_is_ok = $this->getUser()->matchEditToken($token);
      
      //form textfield. Is validated later on
      }elseif($value === 'wptextfield'){
        $this->textfield_array[$original_value] = $request->getText($original_value);
        $this->button_name = 'submitedit';
        
      //form textfield. Is validated later on  
      }elseif($value === 'wptitlefield'){
        $this->manuscript_new_title = $request->getText($original_value);
        $this->button_name = 'submittitle';
        
      }elseif($value === 'edit_selectedcollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        
      }elseif($value === 'linkcollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'editmetadata'; 
        
      }elseif($value === 'linkback'){
        $this->linkback = $this->validateLink($request->getText($value));
        
      }elseif($value === 'manuscriptoldtitle'){  
        $this->manuscript_old_title = $this->validateInput($request->getText('manuscriptoldtitle'));

      }elseif($value === 'singlecollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'singlecollection';
        break;
                
      }elseif($value === 'selectedcollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'editmetadata';
        break;
        
      }elseif($value === 'manuscripturloldtitle'){  
        $this->manuscript_url_old_title = $this->validateLink($request->getText('manuscripturloldtitle'));

      }elseif($value === 'changetitle_button'){ 
        preg_match_all('!\d+!', $original_value, $matches);
        $number = intval($matches[0][0]);
        
        $this->manuscript_old_title = $this->validateInput($request->getText('oldtitle' . $number));
        $this->manuscript_url_old_title = $this->validateLink($request->getText('urloldtitle' . $number));
        $this->button_name = 'changetitle';
                   
      //get offset, if it is available. The offset specifies at which place in the database the query should begin relative to the start  
      }elseif ($value === 'offset'){
        $string = $request->getText($value);      
        $int = (int)$string;
      
        if($int >= 0){
          $this->offset = $int;             
        }else{
          return false; 
        }        
      }
    }
    
    //if there is no button, there was no correct request
    if(!isset($this->button_name) || $this->token_is_ok === false || $this->selected_collection === false || $this->linkback === false
        || $this->manuscript_old_title === false || $this->manuscript_old_title === false || $this->manuscript_url_old_title === false){
      return false;
    }  
    
    if($this->offset >= $this->max_on_page){
      $this->previous_page_possible = true; 
    }
           
    return true; 
  }
}
