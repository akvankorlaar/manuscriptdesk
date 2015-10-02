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

class SpecialUserPage extends SpecialPage {
  
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
      if($value === 'viewmanuscripts'){
        $this->view_manuscripts = true; 
        $this->id_manuscripts = 'button-active';
        $this->button_name = $value; 
        
      }elseif($value === 'viewcollations'){
        $this->view_collations = true; 
        $this->id_collations = 'button-active';
        $this->button_name = $value;   
        
      }elseif($value === 'viewcollections'){
        $this->view_collections = true; 
        $this->id_collections = 'button-active';
        $this->button_name = $value;
        
      }elseif($value === 'wpEditToken'){
        $token = $request->getText($value);
        $this->token_is_ok = $this->getUser()->matchEditToken($token);
        $this->button_name = 'submitedit';
        
      }elseif($value === 'wptextfield'){
        $this->textfield_array[$original_value] = $request->getText($original_value);
        
      }elseif($value === 'edit_selectedcollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        
      }elseif($value === 'linkcollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'editmetadata'; 
        
      }elseif($value === 'linkback'){
        $this->linkback = $this->validateLink($request->getText($value));
        
      }elseif($value === 'singlecollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'singlecollection';
        break;
        
      }elseif($value === 'selectedcollection'){
        $this->selected_collection = $this->validateInput($request->getText($value));
        $this->button_name = 'editmetadata';
        break;
           
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
    if(!isset($this->button_name) || $this->token_is_ok === false || $this->selected_collection === false || $this->linkback === false){
      return false;
    }  
    
    if($this->offset >= $this->max_on_page){
      $this->previous_page_possible = true; 
    }
           
    return true; 
  }
  
  /**
   * This function validates input sent by the client
   * 
   * @param type $input
   */
  private function validateInput($input){
    
    //check for empty variables or unusually long string lengths
    if(!ctype_alnum($input) || $input === null || strlen($input) > 500){
      return false; 
    }
    
    return $input; 
  }
  
  /**
   * 
   */
  private function validateLink($link){
    
    //allowed charachters: alphanumeric, : and /
    if(!preg_match("/^[A-Za-z0-9:\/]+$/",$link) || strlen($link) > 500){  
      return false;
    }
    
    return $link; 
  }
    
  /**
   * This function calls processRequest() if a request was posted, or calls showDefaultPage() if no request was posted
   */
  public function execute(){
    
    $out = $this->getOutput();
    $user_object = $this->getUser();
        
    if(!in_array('ManuscriptEditors',$user_object->getGroups())){
      return $out->addHTML($this->msg('newmanuscript-nopermission'));
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
    
    $button_name = $this->button_name;
    $user_name = $this->user_name;
    
    if($button_name === 'singlecollection'){             
      $summary_page_wrapper = new summaryPageWrapper($button_name,0,0,$user_name,"","",$this->selected_collection);
      $single_collection_data = $summary_page_wrapper->retrieveFromDatabase(); 
      return $this->showSingleCollection($single_collection_data);
    }
    
    if($button_name === 'editmetadata'){
      $summary_page_wrapper = new summaryPageWrapper($button_name,0,0,$user_name,"","",$this->selected_collection);
      $meta_data = $summary_page_wrapper->retrieveFromDatabase();
      return $this->showEditMetadata($meta_data, ''); 
    }
    
    if($button_name === 'submitedit'){
      return $this->processEdit();
    }
      
    if($button_name === 'viewmanuscripts' || $button_name === 'viewcollations' || $button_name === 'viewcollections'){
      $summary_page_wrapper = new summaryPageWrapper($button_name, $this->max_on_page, $this->offset, $user_name);
      list($title_array, $this->next_offset, $this->next_page_possible) = $summary_page_wrapper->retrieveFromDatabase();
      return $this->showPage($title_array);          
    }   
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
        if($index !== 'wptextfield14'){
          if(strlen($textfield) > $max_length){                  
            return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " ". $max_length . " " . $this->msg('userpage-error-editmax2'));
          }elseif(!preg_match("/^[A-Za-z0-9\s]+$/",$textfield)){              
            return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric'));
          }  

        //in case the textfield is the 'notes' textfield  
        }else{
          
          $length_textfield = strlen($textfield);
          $max_charachters_notes = $max_length*20; 
          
          if($length_textfield > $max_charachters_notes){
            return $this->showEditMetadata(array(), $this->msg('userpage-error-editmax1') . " " . $max_charachters_notes . " " . $this->msg('userpage-error-editmax3') . " ". $length_textfield . " " . $this->msg('userpage-error-editmax4'));
          }elseif(!preg_match("/^[A-Za-z0-9,.;!?\s]+$/",$textfield)){  
            return $this->showEditMetadata(array(), $this->msg('userpage-error-alphanumeric2'));
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
   * This function shows a confirmation of the edit after submission of the form, in case the user has reached the page via the link on a manuscript page
   * 
   * @return boolean
   */
  private function prepareRedirect(){
    
    $linkback = $this->linkback; 
    $article_url = $this->article_url;
    $user_name = $this->user_name; 
    $out = $this->getOutput();
    $html = "";
    
    $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

    $html = "";
    $html .= $this->getMenuBar('edit');   
    $html .= $this->addSummaryPageLoader();
    
    $html .= "<div id='userpage-singlecollectionwrap'>";
    
    $html .= "<p>" . $this->msg('userpage-editcomplete') . "</p>"; 
    
    $html .= "<form id='userpage-linkback' action='" . $article_url . $linkback . "' method='post'>";
    $html .= "<input type='submit' class='button-transparent' name='linkback' title='" . $this->msg('userpage-linkback1') . "' value='" . $this->msg('userpage-linkback2') . $linkback . "'>";
    $html .= "</form>"; 
      
    $html .= "</div>";
            
    return $out->addHTML($html);
  }
    
  /**
   * This function constructs the edit form for editing metadata.
   * 
   * See https://www.mediawiki.org/wiki/HTMLForm/tutorial for information on the MediaWiki form builder
   */
  private function showEditMetadata($meta_data = array(), $error = ''){
    
    foreach($meta_data as $index => &$variable){
      $variable = htmlspecialchars($variable);
    }
    
    $metatitle =         isset($meta_data['collections_metatitle']) ? $meta_data['collections_metatitle'] : '';
    $metaauthor =        isset($meta_data['collections_metaauthor']) ? $meta_data['collections_metaauthor'] : '';
    $metayear =          isset($meta_data['collections_metayear']) ? $meta_data['collections_metayear'] :'';
    $metapages =         isset($meta_data['collections_metapages']) ? $meta_data['collections_metapages'] : '';
    $metacategory =      isset($meta_data['collections_metacategory']) ? $meta_data['collections_metacategory'] : '';
    $metaproduced =      isset($meta_data['collections_metaproduced']) ? $meta_data['collections_metaproduced'] : '';
    $metaproducer =      isset($meta_data['collections_metaproducer']) ? $meta_data['collections_metaproducer'] : '';
    $metaeditors =       isset($meta_data['collections_metaeditors']) ? $meta_data['collections_metaeditors'] : '';
    $metajournal =       isset($meta_data['collections_metajournal']) ? $meta_data['collections_metajournal'] : '';
    $metajournalnumber = isset($meta_data['collections_metajournalnumber']) ? $meta_data['collections_metajournalnumber'] : '';
    $metatranslators =   isset($meta_data['collections_metatranslators']) ? $meta_data['collections_metatranslators'] : '';
    $metawebsource =     isset($meta_data['collections_metawebsource']) ? $meta_data['collections_metawebsource'] : '';
    $metaid =            isset($meta_data['collections_metaid']) ? $meta_data['collections_metaid'] : '';
    $metanotes =         isset($meta_data['collections_metanotes']) ? $meta_data['collections_metanotes'] : '';
    
    $out = $this->getOutput(); 
    $user_name = $this->user_name;
    $article_url = $this->article_url;
    $selected_collection = $this->selected_collection;
    
    $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

    $html = "";
    $html .= $this->getMenuBar('edit'); 
    $html .= $this->addSummaryPageLoader();
        
    $html .= "<div id='userpage-singlecollectionwrap'>"; 
    $html .= "<h2>" . $this->msg('userpage-editmetadata') . " ". $selected_collection . "</h2>";
    $html .= $this->msg('userpage-optional');
    $html .= "<br><br>";
      
    if(!empty($error)){
      $html .= "<div class='error'>" . $error . "</div>";  
    }
    
    $html .= "</div>";
    
    $out->addHTML($html);
        
    $max_length = $this->max_length;   
    $descriptor = array();
    
    $descriptor['textfield1'] = array(
      //change to label-message for i18n support
        'label-message' => 'metadata-title', 
        'class' => 'HTMLTextField',
        'default' => $metatitle,
        'maxlength' => $max_length,
         );
    
    $descriptor['textfield2'] = array(
        'label-message' => 'metadata-name', 
        'class' => 'HTMLTextField',
        'default' => $metaauthor,
        'maxlength' => $max_length,
         );
    
    $descriptor['textfield3'] = array(
        'label-message' => 'metadata-year', 
        'class' => 'HTMLTextField',
        'default' => $metayear,
        'maxlength' => $max_length,
         );

    $descriptor['textfield4'] = array(
        'label-message' => 'metadata-pages', 
        'class' => 'HTMLTextField',
        'default' => $metapages,
        'maxlength' => $max_length,
         );

    $descriptor['textfield5'] = array(
       'label-message' => 'metadata-category', 
       'class' => 'HTMLTextField',
       'default' => $metacategory,
       'maxlength' => $max_length,
       );
        
    $descriptor['textfield6'] = array(
      'label-message' => 'metadata-produced', 
      'class' => 'HTMLTextField',
      'default' => $metaproduced,
      'maxlength' => $max_length,
     );

    $descriptor['textfield7'] = array(
      'label-message' => 'metadata-producer', 
      'class' => 'HTMLTextField',
      'default' => $metaproducer,
      'maxlength' => $max_length,
     );
        
     $descriptor['textfield8'] = array(
      'label-message' => 'metadata-editors', 
      'class' => 'HTMLTextField',
      'default' => $metaeditors,
      'maxlength' => $max_length,
     );
            
     $descriptor['textfield9'] = array(
      'label-message' => 'metadata-journal', 
      'class' => 'HTMLTextField',
      'default' => $metajournal,
      'maxlength' => $max_length,
     );
                
     $descriptor['textfield10'] = array(
      'label-message' => 'metadata-journalnumber', 
      'class' => 'HTMLTextField',
      'default' => $metajournalnumber,
      'maxlength' => $max_length,
     );
     
     $descriptor['textfield11'] = array(
      'label-message' => 'metadata-translators', 
      'class' => 'HTMLTextField',
      'default' => $metatranslators,
      'maxlength' => $max_length,
     );
         
     $descriptor['textfield12'] = array(
      'label-message' => 'metadata-websource', 
      'class' => 'HTMLTextField',
      'default' => $metawebsource,
      'maxlength' => $max_length,
     );

    $descriptor['textfield13'] = array(
      'label-message' => 'metadata-id', 
      'class' => 'HTMLTextField',
      'default' => $metaid,
      'maxlength' => $max_length,
     );

     $descriptor['textfield14'] = array(
       'type' => 'textarea',
       'labelmessage' => 'metadata-notes',
       'default' => $metanotes,
       'rows' => 20,
       'cols' => 20,
       'maxlength'=> ($max_length * 10),
     );
     
     if(isset($this->linkback)){
       
     $descriptor['hidden'] = array(
       'type' => 'hidden',
       'name' => 'linkback',
       'default' => $this->linkback, 
        );
     }
               
    $html_form = new HTMLForm($descriptor, $this->getContext());
    $html_form->setSubmitText($this->msg('metadata-submit'));
    $html_form->addHiddenField('edit_selectedcollection', $this->selected_collection);
    $html_form->setSubmitCallback(array('SpecialUserPage', 'processInput'));  
    $html_form->show();
  }
  
    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     * 
     * @param type $formData
     * @return string|boolean
     */
  static function processInput($form_data){ 
    return false; 
  }
  
  /**
   * This function displays a single collection (metadata and information on the pages) to the user
   * 
   * @param type $pages_within_collection
   * @return type
   */
  private function showSingleCollection($single_collection_data){
    
    $out = $this->getOutput(); 
    $user_name = $this->user_name;
    $article_url = $this->article_url;
    $selected_collection = $this->selected_collection;
    list($meta_data, $pages_within_collection) = $single_collection_data; 
    
    $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

    $html = "";
    $html .= $this->getMenuBar();
    $html .= $this->addSummaryPageLoader();
    
    $html .= "<div id='userpage-singlecollectionwrap'>"; 
    
    $html .= "<form id='userpage-editmetadata' action='" . $article_url . "Special:UserPage' method='post'>";
    $html .= "<input type='submit' class='button-transparent' name='editmetadata' value='" . $this->msg('userpage-editmetadatabutton') . "'>";
    $html .= "<input type='hidden' name='selectedcollection' value='" . $selected_collection . "'>";
    $html .= "</form>";
    
    //redirect to Special:NewManuscript, and automatically have the current collection selected
    $html .= "<form id='userpage-addnewpage' action='" . $article_url . "Special:NewManuscript' method='post'>";
    $html .= "<input type='submit' class='button-transparent' name='addnewpage' title='" . $this->msg('userpage-newcollection') . "' value='Add New Page'>";
    $html .= "<input type='hidden' name='selected_collection' value='" . $selected_collection . "'>";
    $html .= "</form>"; 
        
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
    $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
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
   
    return $out->addHTML($html);
  }
  
  /**
   * This function adds html used for the summarypage loader (see ext.summarypageloader)
   */
  private function addSummaryPageLoader(){
        
    //shows after submit has been clicked
    $html  = "<h3 id='summarypage-loaderdiv' style='display: none;'>Loading";
    $html .= "<span id='summarypage-loaderspan'></span>";
    $html .= "</h3>";
    
    return $html; 
  }
  
  /**
   * This function constructs the menu bar
   * 
   * @return string
   */
  private function getMenuBar($context = null){
    
    $article_url = $this->article_url; 
            
    $manuscripts_message = $this->msg('userpage-mymanuscripts');
    $collations_message = $this->msg('userpage-mycollations');
    $collections_message = $this->msg('userpage-mycollections');
    
    $id_manuscripts = isset($this->id_manuscripts) ? $this->id_manuscripts : 'button';
    $id_collations = isset($this->id_collations) ? $this->id_collations : 'button';
    
    if(isset($this->id_collections) && $context === null){
      $id_collections = $this->id_collections; 
    }elseif($context === 'default'){
      $id_collections = 'button'; 
    }elseif($context === 'edit'){
      $id_collections = 'button-active';    
    }

    $html =  '<form class="summarypage-form" action="' . $article_url . 'Special:UserPage" method="post">';
    $html .= "<input type='submit' name='viewmanuscripts' id='$id_manuscripts' value='$manuscripts_message'>"; 
    $html .= "<input type='submit' name='viewcollations' id='$id_collations' value='$collations_message'>"; 
    $html .= "<input type='submit' name='viewcollections' id='$id_collections' value='$collections_message'>";   
    $html .= '</form>';
    
    return $html; 
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
        
    $html = "";
    $html .= $this->getMenuBar();   
    $html .= $this->addSummaryPageLoader();
        
    if(empty($title_array)){
      
      $html .= "<div id='userpage-messagewrap'>";
           
      if($this->view_manuscripts){
        $html .= "<p>" . $this->msg('userpage-nomanuscripts') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $this->msg('userpage-newmanuscriptpage') . "</a></p>";
      }
      
      if($this->view_collations){       
        $html .= "<p>" . $this->msg('userpage-nocollations') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:BeginCollate'>" . $this->msg('userpage-newcollation') . "</a></p>";
      }
      
      if($this->view_collections){       
        $html .= "<p>" . $this->msg('userpage-nocollections') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $this->msg('userpage-newcollection') . "</a></p>";
      }
      
      $html .= "</div>";
      
      return $out->addHTML($html);
    }
    
    if($this->previous_page_possible){
      
      $previous_offset = ($this->offset)-($this->max_on_page); 
      
      $previous_message_hover = $this->msg('allmanuscriptpages-previoushover');
      $previous_message = $this->msg('allmanuscriptpages-previous');
      
      $html .='<form class="summarypage-form" id="previous-link" action="' . $article_url . 'Special:UserPage" method="post">';      
      $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
      $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
      $html .= "<input type='submit' name = 'redirect_page_back' class='button-transparent' title='$previous_message_hover' value='$previous_message'>";      
      $html.= "</form>";
    }
    
    if($this->next_page_possible){
      
      if(!$this->previous_page_possible){
        $html.='<br>';
      }
      
      $next_message_hover = $this->msg('allmanuscriptpages-nexthover');    
      $next_message = $this->msg('allmanuscriptpages-next');
      
      $html .='<form class="summarypage-form" id="next-link" action="' . $article_url . 'Special:UserPage" method="post">';           
      $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
      $html .="<input type='hidden' name='$this->button_name' value='$this->button_name'>"; 
      $html .= "<input type='submit' name = 'redirect_page_forward' class='button-transparent' title='$next_message_hover' value='$next_message'>";     
      $html.= "</form>";
    }
        
    $created_message = $this->msg('userpage-created');
    $html .= "<br>";
        
    if($this->view_manuscripts){
      
      $html .= "<p>" . $this->msg('userpage-manuscriptinstr') . "</p>";        
      $html .= "<table id='userpage-table' style='width: 100%;'>";
      $html .= "<tr>";
      $html .= "<td class='td-long'><b>" . $this->msg('userpage-tabletitle') . "</b></td>";
      $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
      $html .= "</tr>";
      
      foreach($title_array as $key=>$array){

        $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
        $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
        $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';
        
        $html .= "<tr>";
        $html .= "<td class='td-long'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" . 
          htmlspecialchars($title) . "</a></td>";
        $html .= "<td>" . htmlspecialchars($date) . "</td>";
        $html .= "</tr>";      
      }
      
      $html .= "</table>";
    }   
            
    if($this->view_collations){
      
      $html .= "<table id='userpage-table' style='width: 100%;'>";
      $html .= "<tr>";
      $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
      $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
      $html .= "</tr>";
           
      foreach($title_array as $key=>$array){

        $url = isset($array['collations_url']) ? $array['collations_url'] : '';
        $date = isset($array['collations_date']) ? $array['collations_date'] : '';
        $title = isset($array['collations_main_title']) ? $array['collations_main_title'] : '';
        
        $html .= "<tr>";
        $html .= "<td class='td-long'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" . 
          htmlspecialchars($title) . "</a></td>";
        $html .= "<td>" . htmlspecialchars($date) . "</td>"; 
        $html .= "</tr>";
      }    
      
      $html .= "</table>";
    }
    
    if($this->view_collections){
         
      $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $article_url . "Special:UserPage' method='post'>";
      $html .= "<table id='userpage-table' style='width: 100%;'>";
      $html .= "<tr>";
      $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
      $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
      $html .= "</tr>";
      
      foreach($title_array as $key=>$array){
        
        $collections_title = isset($array['collections_title']) ? $array['collections_title'] : '';
        $collections_date = isset($array['collections_date']) ? $array['collections_date'] : '';
        
        $html .= "<tr>";
        $html .= "<td class='td-long'><input type='submit' class='userpage-collectionlist' name='singlecollection' value='" . htmlspecialchars($collections_title) . "'></td>";
        $html .= "<td>" . htmlspecialchars($collections_date) . "</td>";
        $html .= "</tr>";
     }
     
     $html .= "</table>";
     $html .= "<input type='hidden' name='viewcollections' value='viewcollections'>";      
     $html .= "</form>"; 
    }
    
    return $out->addHTML($html); 
  }
  
  /**
   * This function shows the default page if no request was posted 
   */
  private function showDefaultPage(){
      
    $out = $this->getOutput();
    $article_url = $this->article_url;    
    $user_name = $this->user_name; 
    
    $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
    
    $html = "";
    $html .= $this->getMenuBar('default');
    $html .= $this->addSummaryPageLoader();
        
    //if the current user is a sysop, display how much space is still left on the disk
    if($this->sysop){
      $free_disk_space_bytes = disk_free_space($this->primary_disk);
      $free_disk_space_mb = round($free_disk_space_bytes/1048576); 
      $free_disk_space_gb = round($free_disk_space_mb/1024);
      
      $admin_message1 = $this->msg('userpage-admin1');
      $admin_message2 = $this->msg('userpage-admin2');
      $admin_message3 = $this->msg('userpage-admin3');
      $admin_message4 = $this->msg('userpage-admin4');
            
      $html.= "<p>" . $admin_message1 . ' ' . $free_disk_space_bytes . ' ' . $admin_message2 . ' ' . $free_disk_space_mb . ' ' . $admin_message3 . ' ' . $free_disk_space_gb . ' ' . $admin_message4 . ".</p>";
    }
    
    return $out->addHTML($html);
  } 
}

