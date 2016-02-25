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

class SpecialUserPage extends ManuscriptDeskBaseSpecials {
  
/**
 * SpecialuserPage. Organises all content created by a user
 */
  
  public $article_url; 
  public $max_length; 
    
  private $button_name; //value of the button the user clicked on 
  private $max_on_page; //maximum manuscripts shown on a page
  private $next_page_possible;
  private $previous_page_possible;   
  private $offset; 
  private $next_offset; 
  private $user_name; 
  private $view_manuscripts;
  private $view_collations;
  private $view_collections;
  private $sysop;
  private $primary_disk;
  private $id_manuscripts;
  private $id_collations;
  private $id_collections; 
  private $selected_collection;
  private $textfield_array;
  private $token_is_ok; 
  private $linkback;
  private $manuscript_old_title;
  private $manuscript_url_old_title; 
  private $manuscript_new_title; 
  
  public function __construct(){
    
    global $wgNewManuscriptOptions, $wgPrimaryDisk, $wgArticleUrl; 
        
    $this->max_on_page = $wgNewManuscriptOptions['max_on_page'];
    
    $this->next_page_possible = false;//default value
    $this->previous_page_possible = false;//default value
    
    $this->view_manuscripts = false;//default value
    $this->view_collations = false; //default value
    $this->view_collections = false;//default value
    
    $this->token_is_ok = null;//default value
                    
    $this->offset = 0;//default value
    
    $this->sysop = false; //default value
    
    $this->primary_disk = $wgPrimaryDisk; 
    
    $this->id_manuscripts = 'button';
    $this->id_collations = 'button';
    $this->id_collections = 'button';
    $this->max_length = 50;
    
    $this->textfield_array = array();
    
    $this->linkback = null; //default value
    
    parent::__construct('UserPage');
  }
      
  /**
   * This function calls processRequest() if a request was posted, or calls showDefaultPage() if no request was posted
   */
  public function execute(){
          
    $request_was_posted = $this->loadRequest();
    
    if($request_was_posted){
      return $this->processRequest();
    }
    
    return $this->getDefaultPage();
  }
  
  private function getDefaultPage(){
      
  }
  
  /**
   * This function processes the request if it was posted
   */
  private function processRequest(){
    
    $button_name = $this->button_name;
    $user_name = $this->user_name;
        
    if($button_name === 'editmetadata'){
      $summary_page_wrapper = new summaryPageWrapper($button_name,0,0,$user_name,"","",$this->selected_collection);
      $meta_data = $summary_page_wrapper->retrieveFromDatabase();
      return $this->showEditMetadata($meta_data, ''); 
    }
    
    if($button_name === 'changetitle'){
      return $this->showEditTitle();
    }
    
    if($button_name === 'submitedit'){
      return $this->processEdit();
    }
    
    if($button_name === 'submittitle'){
      return $this->processNewTitle();
    }
    
    if($button_name === 'singlecollection'){             
      $summary_page_wrapper = new summaryPageWrapper($button_name,0,0,$user_name,"","",$this->selected_collection);
      $single_collection_data = $summary_page_wrapper->retrieveFromDatabase(); 
      return $this->showSingleCollection($single_collection_data);
    }
          
    if($button_name === 'viewmanuscripts' || $button_name === 'viewcollations' || $button_name === 'viewcollections'){
      $summary_page_wrapper = new summaryPageWrapper($button_name, $this->max_on_page, $this->offset, $user_name);
      list($title_array, $this->next_offset, $this->next_page_possible) = $summary_page_wrapper->retrieveFromDatabase();
      return $this->showPage($title_array);          
    }   
  }
  
  /**
   * This function processes the edit when submitting a new manuscript page title
   */
  private function processNewTitle(){
    
    global $wgWebsiteRoot, $wgNewManuscriptOptions; 
    
    $web_root = $wgWebsiteRoot;   
    $zoomimages_dirname = $wgNewManuscriptOptions['zoomimages_root_dir'];
    $original_images_dir = $wgNewManuscriptOptions['original_images_dir'];
    $manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
    
    $user_name = $this->user_name; 
    $manuscript_new_title = $this->manuscript_new_title;
    $manuscript_old_title = $this->manuscript_old_title;
    $selected_collection = $this->selected_collection; 
    $manuscript_url_old_title = $this->manuscript_url_old_title;
    $max_length = $this->max_length; 
         
    //if the new title and the old title are equal, do nothing and return 
    if($manuscript_new_title === $manuscript_old_title){   
      $summary_page_wrapper = new summaryPageWrapper('submitedit',0,0,$user_name,"","", $selected_collection);
      $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();          
      return $this->showSingleCollection($single_collection_data); 
    }
     
    //check for errors in $manuscript_new_title
    if(empty($manuscript_new_title)){
      return $this->showEditTitle($this->msg('userpage-error-empty'));  
    
    }elseif(strlen($manuscript_new_title) > $max_length){                  
      return $this->showEditTitle($this->msg('userpage-error-editmax1') . " ". $max_length . " " . $this->msg('userpage-error-editmax2'));
            
    //allow only alphanumeric charachters 
    }elseif(!preg_match("/^[A-Za-z0-9]+$/",$manuscript_new_title)){              
      return $this->showEditTitle($this->msg('userpage-error-alphanumeric'));
    }
    
    $new_page_url = trim($manuscripts_namespace_url . $user_name . '/' . $manuscript_new_title);

    if(null !== Title::newFromText($new_page_url)){
      
      $title_object = Title::newFromText($new_page_url);
      
      if($title_object->exists()){
        return $this->showEditTitle($this->msg('userpage-error-exists'));  
      }
      
    }else{
      return $this->showEditTitle($this->msg('userpage-error-exists')); 
    }  
            
    $old_zoomimages = $web_root . DIRECTORY_SEPARATOR . $zoomimages_dirname . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_old_title; 
    $new_zoomimages = $web_root . DIRECTORY_SEPARATOR . $zoomimages_dirname . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_new_title; 
    
    $old_original_images = $web_root . DIRECTORY_SEPARATOR . $original_images_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_old_title; 
    $new_original_images = $web_root . DIRECTORY_SEPARATOR . $original_images_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $manuscript_new_title; 
   
    //if the directories do not exist, do nothing and return 
    if(!is_dir($old_zoomimages) && !is_dir($old_original_images)){
      $summary_page_wrapper = new summaryPageWrapper('submitedit',0,0,$user_name,"","", $selected_collection);
      $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();            
      return $this->showSingleCollection($single_collection_data);
    }
           
    //rename the zoomimages folder and the original images folder
    rename($old_zoomimages, $new_zoomimages);
    rename($old_original_images, $new_original_images);
    
    //get text from old wikipage
    $title_object = Title::newFromText($manuscript_url_old_title);  
    $article_object = Wikipage::factory($title_object);  
    $old_page_text = $article_object->getRawText();
           
    //create a new wikipage with the $old_page_text
    $title_object = Title::newFromText($new_page_url);  
    $context = $this->getContext();  
    $article = Article::newFromTitle($title_object, $context);        
    $editor_object = new EditPage($article); 
    $content_new = new wikitextcontent($old_page_text);
    
    $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97,
                        false, null, $editor_object->contentFormat);
    
    if (!$doEditStatus->isOK()){
      rename($new_zoomimages, $old_zoomimages);
      rename($new_original_images, $old_original_images);
      wfErrorLog($this->msg('userpage-error-wikipage') . $new_page_url . $this->msg('userpage-error3') . $manuscript_url_old_title . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');   
      return $this->showEditTitle($this->msg('userpage-error-wikipage2'));  
    }
    
    $dbw = wfGetDB(DB_MASTER);
    $dbw->begin( __METHOD__ );
           
    //get the page id of the old page, and delete the old page
    $page_title = str_replace('Manuscripts:','',$manuscript_url_old_title);    
    $summary_page_wrapper = new summaryPageWrapper('submitedit',0,0,$user_name,"","",$selected_collection, $page_title);
    $page_id = $summary_page_wrapper->retrievePageId();
    
    $dbw->delete(
      'page', //from
      array(
        'page_id' => $page_id
      ),  //conditions
      __METHOD__
    );
    
    if (!$dbw->affectedRows() > 0){
      $dbw->rollback( __METHOD__ );
      rename($new_zoomimages, $old_zoomimages);
      rename($new_original_images, $old_original_images);
      wfErrorLog( $this->msg('userpage-error-log1') . $new_page_url . $this->msg('userpage-error-log3') . $manuscript_url_old_title . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
      
      return $this->showEditTitle($this->msg('userpage-error-delete'));  
    }
        
    //update the 'manuscripts' table
    $dbw->update(
      'manuscripts', //select table
      array( //update values
      'manuscripts_title' => $manuscript_new_title,
      'manuscripts_url' => $new_page_url, 
      'manuscripts_lowercase_title' => strtolower($manuscript_new_title),
      ),
      array(
        'manuscripts_url  = ' . $dbw->addQuotes($manuscript_url_old_title),//conditions
      ), //conditions
      __METHOD__,
      'IGNORE'
    );
    
    if (!$dbw->affectedRows()){
      $dbw->rollback( __METHOD__ );     
      rename($new_zoomimages, $old_zoomimages);
      rename($new_original_images, $old_original_images);
      wfErrorLog( $this->msg('userpage-error-log3') . $new_page_url . $this->msg('userpage-error-log3') . $manuscript_url_old_title . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
      
      return $this->showEditTitle($this->msg('userpage-error-database'));  
    }   
      
    //redirect back if there were no errors          
    $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();           
    return $this->showSingleCollection($single_collection_data);
  }
  
  /**
   * This function processes the edit form once it has been posted
   * 
   * /^[A-Za-z0-9\s]+$/
   * 
   * / = delimiter
   * ^ and $ = anchors. Start and end of line
   * /s = match spaces
   * 
   * @return string
   */
  private function processEdit(){
    
    $max_length = $this->max_length; 
    $textfield_array = $this->textfield_array;

    foreach($textfield_array as $index=>$textfield){

      if(!empty($textfield)){
        //wptextfield12 is the websource textfield, and wptextfield14 is the notes textfield
        if($index !== 'wptextfield12' && $index !== 'wptextfield14'){
            
          if(strlen($textfield) > $max_length){                  
            return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " ". $max_length . " " . $this->msg('userpage-error-editmax2'));
            
          //allow alphanumeric charachters and whitespace  
          }elseif(!preg_match("/^[A-Za-z0-9\s]+$/",$textfield)){              
            return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric'));
          }  
        
        }elseif ($index === 'wptextfield12'){
          
          if(strlen($textfield) > $max_length){                  
            return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " ". $max_length . " " . $this->msg('userpage-error-editmax2'));
            
          //allow alphanumeric charachters, whitespace, and '-./:'  
          }elseif(!preg_match("/^[A-Za-z0-9\-.\/:\s]+$/",$textfield)){              
            return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric2'));
          }  
          
        }elseif ($index === 'wptextfield14'){
          
          $length_textfield = strlen($textfield);
          $max_charachters_notes = $max_length*20; 
          
          if($length_textfield > $max_charachters_notes){
            return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " " . $max_charachters_notes . " " . $this->msg('userpage-error-editmax3') . " ". $length_textfield . " " . $this->msg('userpage-error-editmax4'));
            
           //allow alphanumeric charachters, whitespace, and ',.;!?' 
          }elseif(!preg_match("/^[A-Za-z0-9,.;!?\s]+$/",$textfield)){  
            return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric3'));
          }  
        }
      }
    }
    
    $summary_page_wrapper = new summaryPageWrapper('submitedit',0,0,$this->user_name,"","", $this->selected_collection);
    $status = $summary_page_wrapper->insertCollections($textfield_array);           
    $single_collection_data = $summary_page_wrapper->retrieveFromDatabase();
    
    if(isset($this->linkback)){
      return $this->prepareRedirect();
    }
    
    return $this->showSingleCollection($single_collection_data);
  }
    
    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
  static function processInput($form_data){ 
    return false; 
  }
  
}