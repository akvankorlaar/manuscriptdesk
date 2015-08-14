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

class SpecialuserPage extends SpecialPage {
  
/**
 * SpecialuserPage. Organises all content created by a user
 * 
 * Possible problems: Displaying the page may become slow. If this happens, try using $out->addHTML instead of $out->addWikiText 
 */
  
  public $article_url; 
    
  private $button_name; //value of the button the user clicked on 
  private $max_on_page; //maximum manuscripts shown on a page
  private $next_page_possible;
  private $previous_page_possible;   
  private $offset; 
  private $user_name; 
  private $view_manuscripts;
  private $view_collations;
  private $view_collections; 
  private $sysop;
  private $primary_disk;
  private $id_manuscripts;
  private $id_collations;
  private $id_collections; 
  
   //class constructor 
	public function __construct(){
    
    global $wgNewManuscriptOptions, $wgPrimaryDisk, $wgArticleUrl; 
    
    $this->article_url = $wgArticleUrl;
    
    $this->max_on_page = $wgNewManuscriptOptions['max_on_page'];
    
    $this->next_page_possible = false;//default value
    $this->previous_page_possible = false;//default value
    
    $this->view_manuscripts = false;//default value
    $this->view_collations = false; //default value
    $this->view_collections = false;//default value
                    
    $this->offset = 0;//default value
    
    $this->sysop = false; //default value
    
    $this->primary_disk = $wgPrimaryDisk; 
    
    $this->id_manuscripts = 'button';
    $this->id_collations = 'button';
    $this->id_collections = 'button';
    
		parent::__construct('userPage');
	}
  
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
    foreach($posted_names as $key=>$value){
      //get the posted button      
      if($value === 'viewmanuscripts'){
        $this->view_manuscripts = true; 
        $this->id_manuscripts = 'button_active';
        $this->button_name = $value; 
        
      }elseif($value === 'viewcollations'){
        $this->view_collations = true; 
        $this->id_collations = 'button_active';
        $this->button_name = $value;   
        
      }elseif($value === 'viewcollections'){
        $this->view_collections = true; 
        $this->id_collections = 'button_active';
        $this->button_name = $value;   
        
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
    if(!isset($this->button_name)){
      return false;
    }  
    
    if($this->offset >= $this->max_on_page){
      $this->previous_page_possible = true; 
    }
    
    return true; 
  }
  
  /**
   * This function calls processRequest() if a request was posted, or calls showDefaultPage() if no request was posted
   */
	public function execute(){
    
    $out = $this->getOutput();
    $user_object = $this->getUser();
        
    if(!in_array('ManuscriptEditors',$user_object->getGroups())){
      return $out->addWikiText($this->msg('newmanuscript-nopermission'));
    }
    
    if(in_array('sysop',$user_object->getGroups())){
      $this->sysop = true;
    }
      
    $this->user_name = $user_object->getName(); 
    
    $request_was_posted = $this->loadRequest();
    
    if($request_was_posted){
      return $this->processRequest();
    }
    
    return $this->showDefaultPage();     
	}
  
  /**
   * This function processes the request if it was posted
   */
  private function processRequest(){
            
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
    
    $user_name = $this->user_name;   
                               
    $conds = array(
    'manuscripts_user = ' . $dbr->addQuotes($user_name),  
    );       
       
    if($this->view_manuscripts){
      
      $conds = array(
        'manuscripts_user = ' . $dbr->addQuotes($user_name),  
      );  

      return $this->retrieveFromManuscripts($dbr,$conds);
    }elseif($this->view_collations){
      
      $conds = array(
        'collations_user = ' . $dbr->addQuotes($user_name),  
      ); 

      return $this->retrieveFromCollations($dbr,$conds);
      
    }elseif($this->view_collections){
      
      $conds = array(
        'manuscripts_user = ' . $dbr->addQuotes($user_name),
        'manuscripts_collection != ' . $dbr->addQuotes(""),
        'manuscripts_collection != ' . $dbr->addQuotes("none"),
      ); 

      return $this->retrieveManuscriptCollections($dbr,$conds);
    }
  }
  
  /**
   * This function retrieves data from the 'manuscripts' table
   * 
   * @return type
   */
  private function retrieveFromManuscripts($dbr,$conds, $title_array = array()){
    
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_collection',
        'manuscripts_lowercase_title',
         ),
      $conds, //conditions
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
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
          'manuscripts_title' => $s->manuscripts_title,
          'manuscripts_url' => $s->manuscripts_url,
          'manuscripts_date' => $s->manuscripts_date,
          'manuscripts_collection' => $s->manuscripts_collection,
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
   * This function retrieves data from the 'collations' table
   * 
   * @param type $dbr
   * @param type $conds
   * @param type $titles_array
   */
  private function retrieveFromCollations($dbr, $conds, $title_array = array()){
    
      //Database query
    $res = $dbr->select(
      'collations', //from
      array(
        'collations_url',//values
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
          'collations_url' => $s->collations_url,
          'collations_date' => $s->collations_date,
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
   * This function retrieves data of manuscripts contained in collections from the 'manuscripts' table
   * 
   * @return type
   */
  private function retrieveManuscriptCollections($dbr, $conds, $title_array = array()){
    
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_date',
        'manuscripts_collection',
         ),
      $conds, //conditions
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_collection',
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
          'manuscripts_title' => $s->manuscripts_title,
          'manuscripts_url' => $s->manuscripts_url,
          'manuscripts_date' => $s->manuscripts_date,
          'manuscripts_collection' => $s->manuscripts_collection,  
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
  private function showPage($title_array){
    
    $out = $this->getOutput();
    
    $article_url = $this->article_url; 
    
    $user_name = $this->user_name; 

    $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
        
    $manuscripts_message = $this->msg('userpage-mymanuscripts');
    $collations_message = $this->msg('userpage-mycollations');
    $collections_message = $this->msg('userpage-mycollections');
    
    $id_manuscripts = $this->id_manuscripts;
    $id_collations = $this->id_collations;
    $id_collections = $this->id_collections; 

    $html ='<form action="' . $article_url . 'Special:userPage" method="post">';
    $html .= "<input type='submit' name='viewmanuscripts' id='$id_manuscripts' value='$manuscripts_message'>"; 
    $html .= "<input type='submit' name='viewcollations' id='$id_collations' value='$collations_message'>"; 
    $html .= "<input type='submit' name='viewcollections' id='$id_collections' value='$collections_message'>";   
    $html .= '</form>';
        
    if(empty($title_array)){
      
      $out->addHTML($html);
      
      if($this->view_manuscripts){
    
        return $out->addWikiText($this->msg('userpage-nomanuscripts'));
      }elseif($this->view_collations){
        
        return $out->addWikiText($this->msg('userpage-nocollations'));
      }elseif($this->view_collections){
        
        return $out->addWikiText($this->msg('userpage-nocollections'));
      }
    }
    
    if($this->previous_page_possible){
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
      
      $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
      $previous_message = $this->msg('allmanuscriptpages-previous');
      
      $html .='<form action="' . $article_url . 'Special:userPage" method="post">';
       
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' name = 'redirect_page_back' id='button' title='$previous_message_hover'  value='$previous_message'>";
      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_message_hover = $this->msg('allmanuscriptpages-nexthover');    
      $next_message = $this->msg('allmanuscriptpages-next');
      
      $html .='<form action="' . $article_url . 'Special:userPage" method="post">';
            
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .="<input type='hidden' name='$this->button_name' value='$this->button_name'>"; 
      $html .= "<input type='submit' name = 'redirect_page_forward' id='button' title='$next_message_hover' value='$next_message'>";
      
      $html.= "</form>";
    }
    
    $out->addHTML($html);
    
    $created_message = $this->msg('userpage-created');
    
    $displayed_collections = array();
    
    if($this->view_manuscripts){
    
      $wiki_text = "";
      
      foreach($title_array as $key=>$array){

        $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
        $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
        $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
        $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
        
        if($collection === "" || $collection === "none"){
          $wiki_text .= '<br><br>[[' . $url . '|' . $title .']] <br>' . $created_message . $date; 
        }else{
          $wiki_text .= '<br><br>[[' . $url . '|' . $title .']] (' . $collection . ')<br>' . $created_message . $date;  
        }
      }   
      
      return $out->addWikiText($wiki_text);
      
    }elseif($this->view_collations){
      
      $wiki_text = "";
      
      foreach($title_array as $key=>$array){

        $url = isset($array['collations_url']) ? $array['collations_url'] : '';
        $date = isset($array['collations_date']) ? $array['collations_date'] : '';
        $title = isset($array['collations_main_title']) ? $array['collations_main_title'] : '';

        
        $wiki_text .= '<br><br>[[' . $url . '|' . $title .']] <br>' . $created_message . $date; 
      }
      
      return $out->addWikiText($wiki_text); 
      
    }elseif($this->view_collections){
         
      $wiki_text = "";
      
      foreach($title_array as $key=>$array){

        $collection = isset($array['manuscripts_collection']) ? $array['manuscripts_collection'] : '';
        $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
        $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
        $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
        
        if(in_array($collection, $displayed_collections)){
          $wiki_text .= '<br><br>[[' . $url . '|' . $title .']] <br>' . $created_message . $date;
          
        }else{
          $wiki_text .= '<br><br>' . "'''" . $collection . ':' . "'''" . '<br><br>' . '[[' . $url . '|' . $title .']] <br>' . $created_message . $date;
          $displayed_collections[] = $collection; 
        }         
      }
      
     return $out->addWikiText($wiki_text);
    }
  }
  
  /**
   * This function shows the default page if no request was posted 
   */
  private function showDefaultPage(){
      
    $out = $this->getOutput();
    
    $article_url = $this->article_url; 
    
    $user_name = $this->user_name; 
    
    $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
    
    $manuscripts_message = $this->msg('userpage-mymanuscripts');
    $collations_message = $this->msg('userpage-mycollations');
    $collections_message = $this->msg('userpage-mycollections');

    $html ='<form action="' . $article_url . 'Special:userPage" method="post">';
    $html .= "<input type='submit' name='viewmanuscripts' id='button' value='$manuscripts_message'>"; 
    $html .= "<input type='submit' name='viewcollations' id='button' value='$collations_message'>"; 
    $html .= "<input type='submit' name='viewcollections' id='button' value='$collections_message'>";   
    $html .= '</form>';
        
    //if the current user is a sysop, display how much space is still left on the disk
    if($this->sysop){
      $free_disk_space_bytes = disk_free_space($this->primary_disk);
      $free_disk_space_mb = round($free_disk_space_bytes/1048576); 
      $free_disk_space_gb = round($free_disk_space_mb/1024);
      
      $admin_message1 = $this->msg('userpage-admin1');
      $admin_message2 = $this->msg('userpage-admin2');
      $admin_message3 = $this->msg('userpage-admin3');
      $admin_message4 = $this->msg('userpage-admin4');
            
      $html.= "<br><br>";
      $html.= $admin_message1 . $free_disk_space_bytes . ' ' . $admin_message2 . ' ' . $free_disk_space_mb . ' ' . $admin_message3 . ' ' . $free_disk_space_gb . $admin_message4 . '.';
    }
    
    return $out->addHTML($html);
  } 
}

