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

class SpecialNewManuscript extends SpecialPage {
  
  /**
   * This class handles file uploads for the newManuscript extension. Some of the functions have been copied from
   * includes/specials/SpecialUpload.php and altered for the purpose of this extension
   */
      
  public $request;
  public $uploadbase_object;
  public $upload_was_clicked;
  public $posted_title;
  public $posted_collection; 
  public $title_ok;
  public $max_upload_size;
  public $maximum_pages_per_collection; 
  
  private $token_is_ok;
  private $allowed_file_extensions;  
  private $target_dir; 
  private $user_name; 
  private $document_root; 
  private $max_manuscripts; 
  private $manuscripts_namespace_url; 
  private $new_page_title_object; 
  private $zoomimages_root_dir; 
  private $selected_collection; 
  
  //class constructor
  public function __construct(){
    
    global $wgNewManuscriptOptions,$wgWebsiteRoot; 
   	 
    $this->max_upload_size = $wgNewManuscriptOptions['max_upload_size'];
    $this->allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];
    $this->max_manuscripts = $wgNewManuscriptOptions['max_manuscripts'];
    $this->maximum_pages_per_collection = $wgNewManuscriptOptions['maximum_pages_per_collection'];
    
    $this->document_root = $wgWebsiteRoot;
    $this->target_dir = $this->document_root . DIRECTORY_SEPARATOR .  $wgNewManuscriptOptions['original_images_dir']; 
    
    $this->manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
    $this->zoomimages_root_dir = $wgNewManuscriptOptions['zoomimages_root_dir'];
        
    parent::__construct('NewManuscript');
  }
  
  /**
   * Load requests
   */
  private function loadRequest($user_object){
    
    $this->request = $request = $this->getRequest();
    $this->uploadbase_object = UploadBase::createFromRequest($request);
    $this->upload_was_clicked = $request->wasPosted() && ($request->getCheck('wpUpload'));
    
    // If it was posted check for the token (no remote POST'ing with user credentials)
    $token = $request->getVal('wpEditToken');
    $this->token_is_ok = $user_object->matchEditToken($token);
    $this->posted_title = $request->getText('wptitle_field');
    $this->posted_collection = $request->getText('wpcollection_field');
    $this->user_name = $user_object->getName();        
    $this->selected_collection = $this->validateInput($request->getText('selected_collection'));  
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
   * This function checks if the user has the right to create new manuscript pages, loads the requests and checks if the request was valid. 
   * If a valid request was posted, this request is processed. Otherwise, the default page is shown, if the user has not reached the maximum allowed manuscript uploads.
   * Also, the page title is set.
   */
  public function execute() {
        
    $out = $this->getOutput();
    $user_object = $out->getUser();
    
    if(!in_array('ManuscriptEditors',$user_object->getGroups())){
      return $out->addHTML($this->msg('newmanuscript-nopermission'));
    }

    $this->loadRequest($user_object);
    
    if ($this->token_is_ok &&($this->uploadbase_object && $this->upload_was_clicked)){
      //the user clicked 'Create New Manuscript Page', and so go to processUpload()
      return $this->processUpload();
    }
 
    //set page title
    $out->setPageTitle($this->msg('newmanuscript'));
        
    $max_uploads_reached = $this->checkNumberOfUploads();
    
    //If the user has already uploaded the maximum amount of allowed manuscript pages, do not display the form
    if($max_uploads_reached){
      return $out->addHTML($this->msg('newmanuscript-maxreached')); 
    }
    
    $this->addNewmanuscriptLoader();
    
    return $this->getUploadForm()->show();
  }
  
  /**
   * This function checks whether the user has reached the maximum of allowed uploads
   */
  private function checkNumberOfUploads(){
        
    $max_manuscripts = $this->max_manuscripts; 
    $zoomimages_root_dir = $this->zoomimages_root_dir;   
    $user_name = $this->user_name;  
    
    $zoomimages_dir = $this->document_root . DIRECTORY_SEPARATOR . $zoomimages_root_dir . DIRECTORY_SEPARATOR . $user_name; 
    
    if(!file_exists($zoomimages_dir)){
      return false; 
    }
    
    //Count the number of files in the directory. The count is subtracted by 2 because the function also counts '.' and '..' as separate directories. 
    $number_of_uploads = count(scandir($zoomimages_dir)) - 2;
    
    if($number_of_uploads <= $max_manuscripts){
      return false;
    }else{
      return true; 
    }
  }
  
  /** 
   * This function gets the uploadform
   * 
   * @param type $message
   * @return type
   */
  private function getUploadForm($message = ''){
   
    $context = new DerivativeContext($this->getContext());
    
    $new_manuscript_wrapper = new newManuscriptWrapper($this->user_name);
    //get the collections of the current user to display the user's current collections
    $collections_current_user = $new_manuscript_wrapper->getCollectionsCurrentUser();
    
    foreach($collections_current_user as $index=> &$value){
      $value = htmlspecialchars($value);
    }
    
    if(!empty($collections_current_user)){
      $collections_string = implode(', ', $collections_current_user);
      
      $collections_message = $this->msg('newmanuscript-collections') . htmlspecialchars($collections_string);
      
    }else{
      $collections_message = "";
    }

    $new_manuscript_form = new newManuscriptForm($context, $collections_message, $this->selected_collection);
        
    //Add upload error message. 
    $new_manuscript_form->addPreText($message);
    
    //This is needed to redisplay the form in case there was an upload error
    $new_manuscript_form->setSubmitCallback(array('SpecialnewManuscript', 'showUploadError2'));
    
    return $new_manuscript_form;
  }
  
  /**
   * This function processes upload requests if the form was posted
   */
  private function processUpload(){
    
    $posted_title = $this->posted_title;
    $uploadbase_object = $this->uploadbase_object;
    $collection_title = $this->posted_collection; 
    $target_dir = $this->target_dir; 
    $user_name = $this->user_name; 
    $collection_error = "";
    
    //check if the $posted_title is valid
    list($new_page_url, $local_url, $title_error) = $this->checkTitle($posted_title);
    
    //set the collection name to "none" if no collection name was given
    if($collection_title === ""){
      $collection_title = "none";
    }else{  
      $collection_error = $this->checkCollection($collection_title);
    }
        
    $target_dir = $target_dir . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $posted_title;
   
    $title_object = $uploadbase_object->getTitle();
    //get the $file_name from the $title_object
    $file_name = isset($title_object) ? $title_object->getText() : "";
    
    if(null !== pathinfo($file_name,PATHINFO_EXTENSION)){
      $extension = pathinfo($file_name,PATHINFO_EXTENSION);
    }else{
      $extension = null; 
    }
    
    $magic = MimeMagic::singleton();
    $temp_path = $uploadbase_object->getTempPath();
    
    //this function tries to guess the mime type by, for example, opening the file, and checking the headers. See 'includes/MimeMagic.php 
    $mime = strtolower($magic->guessMimeType($temp_path));
           
    $target_file = $target_dir . DIRECTORY_SEPARATOR . $posted_title . '.' . $extension;
        
    //check for various aspects that could return an error   
    if($title_error !== "" && $collection_error !== ""){
      return $this->showUploadError($this->msg($title_error) . "<br>" . $this->msg($collection_error));
    }
        
    if($title_error !== ""){
      return $this->showUploadError($this->msg($title_error));
    }
    
    if($collection_error !== ""){
      return $this->showUploadError($this->msg($collection_error));
    }
      
    if($temp_path === ""){
      return $this->showUploadError($this->msg('newmanuscript-error-nofile'));
    }
   
    if(getimagesize($_FILES["wpUploadFile"]["tmp_name"]) === false){
      return $this->showUploadError($this->msg('newmanuscript-error-noimage'));
    }
         
    if (file_exists($target_file)){
      //following error will only trigger if somehow an earlier attempt with this title did not complete (yet). In the case this error triggers, it means
      //that the initial upload exists, but there is no corresponding wiki page (yet), otherwise a $title_error should not be empty.
      // Additional testing needed to see if this error is necessary. 
      return $this->showUploadError($this->msg('newmanuscript-error-page'));         
    }
    
    if ($uploadbase_object->getFileSize() > $this->max_upload_size) {
      return $this->showUploadError($this->msg('newmanuscript-error-toolarge'));
    }
    
    if($extension === ""){
      return $this->showUploadError($this->msg('newmanuscript-error-noextension'));
    }
      
    if(!in_array($extension, $this->allowed_file_extensions)){
      return $this->showUploadError($this->msg('newmanuscript-error-fileformat'));
    }
      
    //strpos should be equaled to false.. not to ! ...
    if(!strpos($mime,$this->allowed_file_extensions[0]) && !strpos($mime,$this->allowed_file_extensions[1]) && !strpos($mime,$this->allowed_file_extensions[2]) && !strpos($mime,$this->allowed_file_extensions[3])){
      return $this->showUploadError($this->msg('newmanuscript-error-fileformat'));
    }
            
    if($uploadbase_object::detectScript($temp_path,$mime,$extension) === true){
      return $this->showUploadError($this->msg('newmanuscript-error-scripts'));
    }
       
    //make the target directory if it does not exist yet
    if (!file_exists($target_dir)) {
      mkdir($target_dir, 0755, true);
    }
    
    $upload_succesfull = move_uploaded_file($temp_path, $target_file); 

    if(!$upload_succesfull){
      wfErrorLog($this->msg('newmanuscript-error-upload') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');   
      return $this->showUploadError($this->msg('newmanuscript-error-upload'));
    }

    $prepare_slicer = new prepareSlicer($posted_title,$target_file, $extension);
    
    //execute the slicer
    $status = $prepare_slicer->execute();

    if($status !== true){
      unlink($target_file);
      
      if(strpos($status,'slicer-error-execute') === true){
        //something went wrong when executing the slicer, so delete all export files, if they exist
        wfErrorLog($status . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');   
        $prepare_slicer->deleteExportFiles();
        $status = 'slicer-error-execute';
      }
      
      //get the error message
      $slicer_error_message = $this->msg($status);

      return $this->showUploadError($slicer_error_message);
    }
    
    //create a new wikipage
    $wikipage_status = $this->createNewWikiPage();
    
    if($wikipage_status !== true){
       //something went wrong when creating a new wikipage, so delete all export files, if they exist
      $prepare_slicer->deleteExportFiles();   
      wfErrorLog($this->msg($wikipage_status) . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');   
      return $this->showUploadError($this->msg($wikipage_status));
    }
    
    $new_manuscript_wrapper = new newManuscriptWrapper();
    
    $date = date("d-m-Y H:i:s");  
    
    if($collection_title !== "none"){
      //store information about the collection in the 'collections' table. Only inserts values if collection does not already exist  
      $collectionstable_status = $new_manuscript_wrapper->storeCollections($collection_title, $user_name, $date);
    }
    
    //store information about the new uploaded manuscript page in the 'manuscripts' table
    $manuscriptstable_status = $new_manuscript_wrapper->storeManuscripts($posted_title, $collection_title, $user_name,$new_page_url, $date);
   
    if(!$manuscriptstable_status){
      //delete all exported files if writing to the database failed, and show an error
      $prepare_slicer->deleteExportFiles(); 
      wfErrorLog($this->msg('newmanuscript-error-database') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');   
      return $this->showUploadError($this->msg('newmanuscript-error-database'));
    }
    
    //insert into alphabetnumbersTable
    $new_manuscript_wrapper->storeAlphabetnumbers($posted_title, $collection_title);    
        
    //redirect to the new page
    return $this->getOutput()->redirect($local_url);
  }
    
 /**
  * This function checks if posted title is empty, contains invalid charachters, is too long, or already exists in the database.
  * 
  * @global type $wgUser
  * @param type $posted_title
  * @return type
  */
  private function checkTitle($posted_title){
    
    $user_name = $this->user_name; 
    $manuscripts_namespace_url = $this->manuscripts_namespace_url; 
    $posted_title = strip_tags($posted_title);
    $title_error = "";
    $new_page_url = "";
    $local_url = null; 

    if($posted_title === ""){
      $title_error = 'newmanuscript-error-notitle';
       
    }elseif(!preg_match('/^[a-zA-Z0-9]*$/', $posted_title)){
      $title_error = 'newmanuscript-error-charachters';
      
    }elseif(strlen($posted_title) > 50){
      $title_error = 'newmanuscript-error-toolong';
        
    }else{
      $user_url = $user_name;
      $new_page_url = trim($manuscripts_namespace_url . $user_url . '/' . $posted_title);
      
      if(null !== Title::newFromText($new_page_url)){
        $title_object = Title::newFromText($new_page_url);
        $local_url = $title_object->getLocalURL();
        
        if($title_object->exists()){
         $title_error = 'newmanuscript-error-exists';
        }
      }else{
        $title_error = 'newmanuscript-error-exists';
      }      
    }
            
    $this->new_page_title_object = isset($title_object) ? $title_object : null; 

    return array($new_page_url, $local_url, $title_error); 
  }
  
  /**
   * This function checks if $posted_collection contains invalid charachters, is too long, or if the collection has reached the maximum allowed manuscript pages
   * 
   * @param type $posted_collection
   * @return string
   */
  private function checkCollection($posted_collection){
    
    if(!preg_match('/^[a-zA-Z0-9]*$/', $posted_collection)){
      $collection_error = 'newmanuscript-error-collectioncharachters';
        
    }elseif(strlen($posted_collection) > 50){
      $collection_error = 'newmanuscript-error-collectiontoolong';
      
    }else{
      $new_manuscript_wrapper = new newManuscriptWrapper($this->user_name, $this->maximum_pages_per_collection);
      $collection_error = $new_manuscript_wrapper->checkTables($posted_collection);
    }
    
    return $collection_error;    
  }
  
  /**
   * This function makes a new wikipage, and auto loads wiki text needed for the metatable.
   */
  private function createNewWikiPage(){
    
    $collection = $this->posted_collection; 
    
    if($collection === 'none'){
      $collection = ''; 
    }
    
    $title_object = $this->new_page_title_object;  
    $context = $this->getContext();  
    $article = Article::newFromTitle($title_object, $context);
             
    $wiki_text = "This page has not been transcribed yet.";
            
    $editor_object = new EditPage($article); 
    $content_new = new wikitextcontent($wiki_text);
    //see includes/EditPage.php of an example on how this function is used
    $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97,
                        false, null, $editor_object->contentFormat);
    
    //when the script has reached this function, the function should never return an error because all the checks have been done beforehand. 
    //However, if unexpectedly the page could not be created, $errors can be inspected to see what the problem was. 
    if (!$doEditStatus->isOK() ) {
			$errors = $doEditStatus->getErrorsArray();
      return 'newmanuscript-error-wikipage';
    }
    
    return true;
  }
  
  /**
   * Show the upload form with error message, but do not stash the file.
   * 
   * @param string $message HTML string
   */
  private function showUploadError($message){
    
    $this->addNewManuscriptLoader();
    
    $message = '<h2>' . $this->msg( 'uploadwarning' )->escaped() . "</h2>\n" .
              '<div class="error">' . $message . "</div>\n";
    
    return $this->getUploadForm($message)->show();
  }
  
  /**
   * This function adds html used for the newmanuscript loader (see ext.newmanuscriptloader)
   * 
   * Source of the gif: http://preloaders.net/en/circular
   */
  private function addNewmanuscriptLoader(){
    
    $out = $this->getOutput();
      //shows after submit has been clicked
    $html  = "<div id='newmanuscript-loaderdiv' style='display: none;'>";
    $html .= "<img id='newmanuscript-loadergif' src='/w/extensions/newManuscript/specials/assets/362.gif' style='width: 64px; height: 64px;"
        . " position: relative; left: 50%;'>"; 
    $html .= "</div>";
    
    $out->addHTML($html);
  }
  
 /**
  * Callback function. Makes sure the page is redisplayed in case there was an error. 
  * 
  * @param type $form_data
  * @return boolean
  */ 
  static function showUploadError2($form_data){
    return false; 
  }
}