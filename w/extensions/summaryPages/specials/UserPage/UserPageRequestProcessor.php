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
    
    public function getDefaultPageData(){
        $request = $this->request;  
        $validator = $this->validator; 
        $posted_names = $request->getValueNames();
        $offset = 0;
        
        foreach ($posted_names as $checkbox) {

            if ($checkbox === 'view_manuscripts_posted' || $checkbox === 'view_collections_posted' || $checkbox === 'view_collations_posted') {
               $button_name = $checkbox;
            }elseif ($value === 'offset'){
                $offset = (int) $validator->validateStringNumber($request->getText($value));

                if (!$offset >= 0) {
                    throw new \Exception('error-request');
                }      
            }
        }
        
        if(!isset($button_name)){
            throw new \Exception('error-request');
        }

        return array($button_name, $offset);
    }
    
    public function singleCollectionPosted() {
        if ($this->request->getText('single_collection_posted') !== '') {
            return true;
        }

        return false;
    }
    
    public function getCollectionTitle(){
        $request = $this->request;
        $validator = $this->validator;      
        return $validator->validateString($request->getText('single_collection'));
    }
    
    public function getLinkBackToManuscriptPage(){
        $request = $this->request;
        $validator = $this->validator;
        $value_name = 'link_back_to_manuscript_page';
        
        if($request->getText($value_name) === ''){
            return ''; 
        }
        
        return $validator->validateStringUrl($request->getText($value_name));
    }
    
  /**
   * This function loads requests when a user selects a button, moves to the previous page, or to the next page
   */
  //private function loadRequest(){
    
    
     
//      //form textfield. Is validated later on
//      }elseif($value === 'wptextfield'){
//        $this->textfield_array[$original_value] = $request->getText($original_value);
//        $this->button_name = 'submitedit';
//        
//      //form textfield. Is validated later on  
//      }elseif($value === 'wptitlefield'){
//        $this->manuscript_new_title = $request->getText($original_value);
//        $this->button_name = 'submittitle';
//        
//      }elseif($value === 'edit_selectedcollection'){
//        $this->selected_collection = $this->validateInput($request->getText($value));
//        
//      }elseif($value === 'linkcollection'){
//        $this->selected_collection = $this->validateInput($request->getText($value));
//        $this->button_name = 'editmetadata'; 
//        
//      }elseif($value === 'linkback'){
//        $this->linkback = $this->validateLink($request->getText($value));
//        
//      }elseif($value === 'manuscriptoldtitle'){  
//        $this->manuscript_old_title = $this->validateInput($request->getText('manuscriptoldtitle'));
//
//      }elseif($value === 'singlecollection'){
//        $this->selected_collection = $this->validateInput($request->getText($value));
//        $this->button_name = 'singlecollection';
//        break;
//                
//      }elseif($value === 'selectedcollection'){
//        $this->selected_collection = $this->validateInput($request->getText($value));
//        $this->button_name = 'editmetadata';
//        break;
//        
//      }elseif($value === 'manuscripturloldtitle'){  
//        $this->manuscript_url_old_title = $this->validateLink($request->getText('manuscripturloldtitle'));
//
//      }elseif($value === 'changetitle_button'){ 
//        preg_match_all('!\d+!', $original_value, $matches);
//        $number = intval($matches[0][0]);
//        
//        $this->manuscript_old_title = $this->validateInput($request->getText('oldtitle' . $number));
//        $this->manuscript_url_old_title = $this->validateLink($request->getText('urloldtitle' . $number));
//        $this->button_name = 'changetitle';
//                   
//    }
//    
//    //if there is no button, there was no correct request
//    if(!isset($this->button_name) || $this->token_is_ok === false || $this->selected_collection === false || $this->linkback === false
//        || $this->manuscript_old_title === false || $this->manuscript_old_title === false || $this->manuscript_url_old_title === false){
//      return false;
//    }  
//    
//    if($this->offset >= $this->max_on_page){
//      $this->previous_page_possible = true; 
//    }
//           
//    return true; 
//  }
}
