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

class SpecialBeginCollate extends SpecialPage {
  
/**  
 * This code can run in a few different contexts:
 * 
 * 1: on normal entry, no request is posted, and the default page, with all the collections and manuscripts of the current user is shown
 * 2: on submit, a collation table is constructed, old tempcollate collate data is deleted, the current collate data is stored in the tempcollate table, and the table is shown
 * 3: when redirecting to start, the default page is shown
 * 4: when saving the table, the data is retrieved from the tempcollate table, saved to the collations table, a new wiki page is created, and the user is redirected to this page 
 * 
 */
  
  public $article_url; 
  
  private $minimum_manuscripts;
  private $maximum_manuscripts; 
  private $user_name;  
  private $full_manuscripts_url; 
  private $posted_titles_array;
  private $collection_array;
  private $collection_hidden_array;
  private $save_table;
  private $error_message;
  private $manuscripts_namespace_url;
  private $redirect_to_start; 
  private $time_identifier; 
   
  //class constructor
  public function __construct(){
    
    global $wgNewManuscriptOptions, $wgArticleUrl, $wgCollationOptions;  
    
    $this->article_url = $wgArticleUrl; 
    
    //if $minimum_manuscripts, $maximum_manuscripts and $max_pages_collection is changed, remember to change the corresponding text in collate.i18n.php
    $this->minimum_manuscripts = $wgCollationOptions['wgmin_collation_pages'];
    $this->maximum_manuscripts = $wgCollationOptions['wgmax_collation_pages'];
                
    $this->save_table = false; //default value
    $this->error_message = false; //default value
    $this->manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
    $this->redirect_to_start = false; //default value
    $this->posted_titles_array = array();
    $this->collection_array = array();
    $this->collection_hidden_array = array();
    $this->time_identifier = null; //default value
    $this->variable_not_validated = false; //default value

    parent::__construct('BeginCollate');
  }
  
  /**
   * This function loads requests when a user submits the collate form
   * 
   * @return boolean
   */
  private function loadRequest(){
    
    $request = $this->getRequest();
        
    //if the request was not posted, return false
    if(!$request->wasPosted()){
      return false;  
    }
    
    $posted_names = $request->getValueNames();    
     
    //identify the button pressed
    foreach($posted_names as $key=>$checkbox){
      
      //remove the numbers from $checkbox to see if it matches to 'text', 'collection', 'collection_hidden', 'redirect_to_start', or 'save_current_table'
      $checkbox_without_numbers = trim(str_replace(range(0,9),'',$checkbox));

      if($checkbox_without_numbers === 'text'){
        $this->posted_titles_array[$checkbox] = $this->validateInput($request->getText($checkbox)); 

      }elseif($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = $this->validateInput(json_decode($request->getText($checkbox)));    
      
      }elseif($checkbox_without_numbers === 'collection_hidden'){
        $this->collection_hidden_array[$checkbox] = $this->validateInput($request->getText($checkbox));
        
      }elseif($checkbox_without_numbers === 'time'){
        $this->time_identifier = $this->validateInput($request->getText('time'));
                
      }elseif($checkbox_without_numbers === 'save_current_table'){
        $this->save_table = true;
       
      }elseif($checkbox_without_numbers === 'redirect_to_start'){
        $this->redirect_to_start = true; 
        break; 
      }
    }
    
    //return false if something went wrong during validation
    if($this->variable_not_validated === true){
      return false; 
    }
    
    if($this->redirect_to_start){
      return false; 
    }
        
    return true; 
  }
  
  /**
   * This function validates input sent by the client
   * 
   * @param type $input
   */
  private function validateInput($input){
    
    if(is_array($input)){
      
      foreach($input as $index => $value){
        $status = $this->validateInput($value);
        
        if(!$status){
          return false; 
        }
      }
      
      return $input; 
    }
    
    //see it does not match any of the following charachters
    if(!preg_match('/^[a-zA-Z0-9:\/]*$/', $input)){
      $this->variable_not_validated = true; 
      return false; 
    }
    
    //check for empty variables or unusually long string lengths
    if($input === null || strlen($input) > 500){
      $this->variable_not_validated = true; 
      return false; 
    }
    
    return $input; 
  }
  
  /**
   * This function determines if the user has the right permissions. If a valid request was posted, this request is processed. Otherwise, the default page is shown 
   */
  public function execute(){
    
    $out = $this->getOutput();
    $user_object = $this->getUser();    
    
    if(!in_array('ManuscriptEditors',$user_object->getGroups())){           
      return $out->addHTML($this->msg('collate-nopermission'));      
    }
      
    $user_name = $user_object->getName();
    $this->user_name = $user_name; 
    
    $this->full_manuscripts_url = $this->manuscripts_namespace_url . $this->user_name . '/';
    
    $request_was_posted = $this->loadRequest();
    
    if($request_was_posted){
      return $this->processRequest();
    }
    
    return $this->prepareDefaultPage($out);   
  }
  
  /**
   * Processes the request when a user has submitted the collate form
   * 
   * @return type
   */
  private function processRequest(){
    
    //check if the user tried to save a collation table
    if($this->save_table){
      return $this->processSaveTable();
    }
             
    //check if the user has checked too few boxes
    if(count($this->posted_titles_array)+count($this->collection_array) < $this->minimum_manuscripts){
      return $this->showError('collate-error-fewtexts');
    }
    
    $collection_count = 0; 
    
    foreach($this->collection_array as $collection_name => $url_array){
      $collection_count += count($url_array);
    }
      
    //check if the user has checked too many boxes
    if(count($this->posted_titles_array)+$collection_count > $this->maximum_manuscripts){
      return $this->showError('collate-error-manytexts');
    }
     
    $texts = $this->constructTexts();
    
    //if returned false, one of the posted pages did not exist
    if(!$texts){
      return $this->showError('collate-error-notexists');
    }
    
    $text_converter = new textConverter();
      
    //convert $texts to json, in a format that it can be accepted by Collatex
    $texts_converted = $text_converter->convertJson($texts);
    
    //send $texts_converted to Collatex, and get the output
    $collatex_output = $text_converter->callCollatex($texts_converted);

    //if the output is an empty string, collatex was not started up or configured properly
    if (!$collatex_output || $collatex_output === ""){
      return $this->showError('collate-error-collatex');
    }
    
    //construct all the titles, used to display the page titles and collection titles in the table
    $titles_array = $this->constructTitles();
    
    //construct an URL for the new page
    list($main_title, $new_url) = $this->makeURL($titles_array);
    
    //time format (Unix Timestamp). This timestamp is used to see how old tempcollate values are
    $time = idate('U');
    
    $status = $this->prepareTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output);
        
    if(!$status){
      return $this->showError('collate-error-database');
    }
           
    $this->showFirstTable($titles_array, $collatex_output, $time);
  }
  
  /**
   * This function intializes the $collate_wrapper, clears the tempcollate table, and inserts new data into the tempcollate table 
   */
  private function prepareTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output){
    
    $collate_wrapper = new collateWrapper($this->user_name);
     
    //delete old entries in the 'tempcollate' table
    $status = $collate_wrapper->clearTempcollate($time);
    
    if(!$status){
      return false;
    }
        
    //store new values in the 'tempcollate' table
    $status = $collate_wrapper->storeTempcollate($titles_array, $main_title, $new_url, $time, $collatex_output);
    
    if(!$status){
      return false;
    }
    
    return true;   
  }
  
  /**
   * This function processes the request when the user wants to save the collation table. Collate data is transferred from the 'tempcollate' table to
   * the 'collations' table, preloaded wikitext is retrieved, a new page is made, and the user is redirected to
   * this page
   */
  private function processSaveTable(){
    
    $user_name = $this->user_name;
    $collate_wrapper = new collateWrapper($this->user_name);
    $time_identifier = $this->time_identifier; 
    
    $status = $collate_wrapper->getTempcollate($time_identifier);
    
    if(!$status){
      return $this->showError('collate-error-database');
    }
    
    list($titles_array, $new_url, $main_title, $main_title_lowercase, $collatex_output) = $status; 
            
    $local_url = $this->createNewPage($new_url);
    
    if(!$local_url){
      return $this->showError('collate-error-wikipage');
    }
    
    $status = $collate_wrapper->storeCollations($new_url, $main_title, $main_title_lowercase, $titles_array, $collatex_output);
    
    if(!$status){
      return $this->showError('collate-error-database');
    }
    
    //save data in alphabetnumbersTable   
    $collate_wrapper->storeAlphabetnumbers($main_title_lowercase);
                  
    //redirect the user to the new page
    return $this->getOutput()->redirect($local_url);  
  }
 
  /**
   * This function creates a new wikipage with preloaded wikitext
   * 
   * @param type $new_url
   */
  private function createNewPage($new_url){
    
    $title_object = Title::newFromText($new_url);
    $local_url = $title_object->getLocalURL();

    $context = $this->getContext(); 
    
    $article = Article::newFromTitle($title_object, $context);
       
    //make a new page
    $editor_object = new EditPage($article);
    $content_new = new wikitextcontent('<!--' . $this->msg('collate-newpage') . '-->');
    $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97,
                        false, null, $editor_object->contentFormat);
    
    if (!$doEditStatus->isOK() ) {
      $errors = $doEditStatus->getErrorsArray();
      return false;
    }
    
    return $local_url;
  }
  
  /**
   *  This function constructs the $titles_array used by the table, and removes the base url   
   */
  private function constructTitles(){
    
    $full_manuscripts_url = $this->full_manuscripts_url; 
    $posted_hidden_collection_titles = array();
        
    if (isset($this->collection_hidden_array)){
      //hidden fields are always sent, and so the correct posted collection titles need to be identified
      foreach($this->collection_hidden_array as $key => $value){
        
        //remove everything except the number
        $number = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
        
        //see if this collection name appears in $this->collection_array
        $collection_match = 'collection' . $number; 
        
        if(isset($this->collection_array[$collection_match])){    
          //if it does appear in $this->collection array, add this collection name to $posted_hidden_collection_titles
          $posted_hidden_collection_titles[$key] = $value;
        }
      }
    }
    
    //merge these two arrays if collections were also checked
    $titles_array = !empty($posted_hidden_collection_titles) ? array_merge($this->posted_titles_array,$posted_hidden_collection_titles) : $this->posted_titles_array; 
        
    foreach($titles_array as &$full_url){

      //remove $full_manuscript_url from each url to get the title
      $full_url = trim(str_replace($full_manuscripts_url,'',$full_url));
    }
        
    return $titles_array;
  }
  
  /**
   * This function loops through all the posted collections and titles, and
   * retrieves the text from the corresponding pages 
   * 
   * @return type
   */
  private function constructTexts(){
    
    //in $texts both single page texts and combined collection texts will be stored 
    $texts = array();

    //collect all single pages
    foreach ($this->posted_titles_array as $file_url){
      
      $title_object = Title::newFromText($file_url);

      //if the page does not exist, return false
      if(!$title_object->exists()){
        return false; 
      }

      //get the text
      $single_page_text = $this->getSinglePageText($title_object);
      
      //check if $single_page_text does not only contain whitespace charachters
      if(ctype_space($single_page_text) || $single_page_text === ''){
        return false;
      }

      //add the text to the array
      $texts[] = $single_page_text; 
    }
  
    if($this->collection_array){
      //for collections, collect all single pages of a collection and merge them together
      foreach($this->collection_array as $collection_name => $url_array){

        $all_texts_for_one_collection = "";

        //go through all urls of a collection
        foreach($url_array as $file_url){

          $title_object = Title::newFromText($file_url);

          if(!$title_object->exists()){
            return false; 
          }

          $single_page_text = $this->getSinglePageText($title_object);

          //add $single_page_text to $single_page_texts
          $all_texts_for_one_collection .= $single_page_text; 
        }  
        
        //check if $all_texts_for_one_collection does not only contain whitespace charachters
        if(ctype_space($all_texts_for_one_collection) || $all_texts_for_one_collection === ''){
          return false;
        }

        //add the combined texts of one collection to $texts
        $texts[] = $all_texts_for_one_collection; 
      }
    }
  
    return $texts; 
  }

  /**
   * This function retrieves the wiki text from a page url
   * 
   * @param type $title_object
   * @return type
   */
  private function getSinglePageText($title_object){
    
    $article_object = Wikipage::factory($title_object);  
    $raw_text = $article_object->getRawText();
    
    $filtered_raw_text = $this->filterText($raw_text);
        
    return $filtered_raw_text; 
  }
    
  /**
   * This function filters out tags, and text in between certain tags. It also trims the text, and adds a single space to the last charachter if needed 
   */
  private function filterText($raw_text){
            
    //filter out the following tags, and all text in between the tags
    
    //pagemetatable tag
    $raw_text = preg_replace('/<pagemetatable>[^<]+<\/pagemetatable>/i', '', $raw_text);
    
    //del tag
    $raw_text = preg_replace('/<del>[^<]+<\/del>/i', '', $raw_text);

    //note tag
    $raw_text = preg_replace('/<note>[^<]+<\/note>/i', '', $raw_text);
    
    //filter out any other tags, but keep all text in between the tags
    $raw_text = strip_tags($raw_text);
        
    //filter out newline charachters and carriage returns, and replace them with a single space
    //$raw_text = preg_replace( '/\r|\n/',' ', $raw_text);
    
    //trim the text
    $raw_text = trim($raw_text);
       
    //check if it is possible to get the last charachter of the page
    if(substr($raw_text, -1) !== false){
      $last_charachter = substr($raw_text, -1);
      
      if($last_charachter !== '-'){
        //If the last charachter of the current page is '-', this may indicate that the first word of the next page 
        //is linked to the last word of this page because they form a single word. In other cases, add a space after the last charachter of the current page 
        $raw_text = $raw_text . ' ';
      }
    }
    
    return $raw_text; 
  }  
      
  /**
   * This function prepares the default page, in case no request was posted
   * 
   * @return type
   */
  private function prepareDefaultPage($out){
        
    $collate_wrapper = new collateWrapper($this->user_name, $this->maximum_manuscripts);   
    list($url_array,$title_array) = $collate_wrapper->getManuscriptTitles();

    if(count($url_array) < $this->minimum_manuscripts){
      
      $article_url = $this->article_url;
      
      $html = "";
      $html .= $this->msg('collate-fewuploads');    
      $html .= "<p><a class='begincollate-transparent' href='" . $article_url . "Special:NewManuscript'>Create a new manuscript page</a></p>";
      
      return $out->addHTML($html);
    }
    
    $collection_urls = $collate_wrapper->checkForManuscriptCollections(); 
    
    return $this->showDefaultPage($url_array,$title_array,$collection_urls, $out);
  }
   
  /**
   * This function makes a new URL, which will be used when the user saves the current table
   * 
   * @global type $wgUser
   * @param type $title_array
   * @return type
   */  
  private function makeURL($title_array){
      
    global $wgUser;  
    $user_name = $this->user_name;
    
    $imploded_title_array = implode('',$title_array);
                
    $year_month_day = date('Ymd');   
    $hours_minutes_seconds = date('his');
        
    return array($imploded_title_array, 'Collations:' . $user_name . "/" . $imploded_title_array . "/" . $year_month_day . "/" . $hours_minutes_seconds); 
   }
   
  /**
   * This function fetches the correct error message, and redirects to showDefaultPage()
   * 
   * @param type $type
   */
  private function showError($type){
    
    $error_message = $this->msg($type);
       
    $this->error_message = $error_message;    
    
    return $this->prepareDefaultPage($this->getOutput());
  }
  
 /**
  * This function adds html used for the begincollate loader (see ext.begincollate)
  * 
  * Source of the gif: http://preloaders.net/en/circular
  */
  private function addBeginCollateLoader(){
    
    //shows after submit has been clicked
    $html  = "<div id='begincollate-loaderdiv'>";
    $html .= "<img id='begincollate-loadergif' src='/w/extensions/collate/specials/assets/362.gif' style='width: 64px; height: 64px;"
        . " position: relative; left: 50%;'>"; 
    $html .= "</div>";
    
    return $html; 
  }
    
  /**
   * This function constructs the HTML collation table, and buttons
   * 
   * @param type $title_array
   * @param type $collatex_output
   */
  private function showFirstTable($title_array,$collatex_output, $time){
    
    $out = $this->getOutput();     
    $article_url = $this->article_url;
    
    $redirect_hover_message = $this->msg('collate-redirecthover');
    $redirect_message = $this->msg('collate-redirect');
    
    $save_hover_message = $this->msg('collate-savehover');
    $save_message = $this->msg('collate-save');
        
    $html = "
       <div id = 'begincollate-buttons'>
            <form class='begincollate-form-two' action='" . $article_url . "Special:BeginCollate' method='post'> 
            <input type='submit' class='begincollate-submitbutton-two' name ='redirect_to_start' title='$redirect_hover_message'  value='$redirect_message'>
            </form>
            
            <form class='begincollate-form-two' action='" . $article_url . "Special:BeginCollate' method='post'> 
            <input type='submit' class='begincollate-submitbutton-two' name= 'save_current_table' title='$save_hover_message' value='$save_message'> 
            <input type='hidden' name='time' value='$time'>  
            </form>
       </div>";
            
    $html .= "<p>" . $this->msg('collate-success') . "</p>"  . "<p>" . $this->msg('collate-tableread') . " " . $this->msg('collate-savetable') . "</p>"; 
   
    $html .= $this->AddBeginCollateLoader();
    
    $collate = new collate();
    
    $html .= "<div id='begincollate-tablewrapper'>";
    
    $html .= $collate->renderTable($title_array, $collatex_output);
    
    $html .= "</div>";
    
    return $out->addHTML($html);
  }
  
  /**
   * This function constructs the HTML for the default page
   * 
   * @param type $url_array
   * @param type $title_array
   * @param type $collection_urls
   * @param type $out
   */
  private function showDefaultPage($url_array,$title_array,$collection_urls, $out){
    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('collate-welcome'));
    
    $about_message = $this->msg('collate-about');
    $version_message = $this->msg('collate-version');  
    $software_message = $this->msg('collate-software');
    $lastedit_message = $this->msg('collate-lastedit');
    
    $html  = "<table id='begincollate-infobox'>";
    $html .= "<tr><th>$about_message</th></tr>";
    $html .= "<tr><td>$version_message</td></tr>";
    $html .= "<tr><td>$software_message <a href= 'http://collatex.net' target='_blank'> Collatex Tools 1.7.0</a>.</td></tr>";
    $html .= "<tr><td id='begincollate-infobox-td'><small>$lastedit_message</small></td></tr>";
    $html .= "</table>";
    
    $html .= "<p>" . $this->msg('collate-instruction1') . "</p>";
    
    if(!empty($collection_urls)){
      $html .= "<p>" . $this->msg('collate-instruction2') .  "</p>";
    }
    
    $html .= "<div id='javascript-error'></div>"; 
        
    if($this->error_message){
     $error_message = $this->error_message;   
     $html .= "<br>";
     $html .= "<div class = 'error'>$error_message</div>";
    }
    
    $manuscript_message = $this->msg('collate-manuscriptpages');
    
    $html .= "<form id='begincollate-form' action='" . $article_url . "Special:BeginCollate' method='post'>";    
    $html .= "<h3>$manuscript_message</h3>";
    $html .= "<table class='begincollate-table'>";
    
    //display a checkbox for each manuscript uploaded by this user
    $a = 0;
    $html .= "<tr>";
    foreach($url_array as $index=>$url){
      
      if(($a % 4) === 0){    
        $html .= "</tr>";
        $html .= "<tr>";    
      }
      
      //get corresponding title
      $title_name = $title_array[$index];
      
      $html .= "<td>";
      $html .="<input type='checkbox' class='begincollate-checkbox' name='text$index' value='" . htmlspecialchars($url) . "'>" . htmlspecialchars($title_name);
      $html .= "</td>";
      $a+=1;
    }
    
    $html .= "</tr>";    
    $html .= "</table>"; 
       
    //if there are manuscript collections
    if(!empty($collection_urls)){
      
      $collection_message = $this->msg('collate-collections');           
      $html .= "<h3>$collection_message</h3>";
      $html .= "<table class='begincollate-table'>";

      $a = 0;
      $html .= "<tr>";
      foreach($collection_urls as $collection_name=>$small_url_array){
        
        if(($a % 4) === 0){  
          $html .= "</tr>";
          $html .= "<tr>";    
        }
        
        $manuscripts_urls = $small_url_array['manuscripts_url'];
        
        foreach($manuscripts_urls as $index=>&$url){
          $url = htmlspecialchars($url);
        }
      
        //encode the array into json to be able to place it in the checkbox value
        $json_small_url_array = json_encode($manuscripts_urls);       
        $manuscript_pages_within_collection = htmlspecialchars(implode(', ',$small_url_array['manuscripts_title']));   
        $collection_text = $this->msg('collate-contains') . $manuscript_pages_within_collection . '.';
                
        //add a checkbox for the collection
        $html .="<td>";
        $html .="<input type='checkbox' class='begincollate-checkbox-col' name='collection$a' value='$json_small_url_array'>" . htmlspecialchars($collection_name);
        $html .="<input type='hidden' name='collection_hidden$a' value='" . htmlspecialchars($collection_name) . "'>"; 
        $html .= "<br>";
        $html .= "<span class='begincollate-span'>" . $collection_text . "</span>"; 
        $html .="</td>";
        $a = ++$a; 
      }
      
      $html .= "</tr>";
      $html .= "</table>";
    }
  
    $html .= "<br><br>"; 
    
    $submit_hover_message = $this->msg('collate-hover');
    $submit_message = $this->msg('collate-submit');
    
    $html .= "<input type='submit' disabled id='begincollate-submitbutton' title = $submit_hover_message value=$submit_message>";   
    $html .="</form>";   
    $html .= "<br>";   
    $html .= $this->AddBeginCollateLoader();
        
    $out->addHTML($html);  
  }
}