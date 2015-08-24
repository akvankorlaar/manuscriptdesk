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

/**
 * Ideas: Only possible to use it on Collections with at least ... 5 pages?
 * 
 * Possible to select multiple collections. However, every single collection must have at least 5 pages
 * 
 * First page: Display a form, with only collections. Error is the user does not have collections. 
 * 
 * Two buttons: Calculate frequent words, and Select these texts and upload your words (.txt file, or coyp paste....)
 * 
 * Submit to the same page
 */

class SpecialStylometricAnalysis extends SpecialPage {
  
  public $article_url; 
  
  private $minimum_collections;
  private $minimum_pages_per_collection; 
  private $user_name;  
  private $full_manuscripts_url; 
  private $collection_array;
  private $collection_hidden_array;
  private $error_message;
  private $manuscripts_namespace_url;
  private $redirect_to_start; 
   
  //class constructor
  public function __construct(){
    
    global $wgNewManuscriptOptions, $wgArticleUrl;  
    
    $this->article_url = $wgArticleUrl; 
    
    $this->minimum_collections = 2; 
    $this->minimum_pages_per_collection = 5; 
    $this->error_message = false; //default value
    
    $this->manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
    $this->redirect_to_start = false;
    $this->collection_array = array();

    parent::__construct('StylometricAnalysis');
	}
  
  /**
   * This function loads requests when a user submits the StylometricAnalysis form
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
      
      //remove the numbers from $checkbox to see if it matches to 'collection', 'collection_hidden', or 'redirect_to_start'
      $checkbox_without_numbers = trim(str_replace(range(0,9),'',$checkbox));

      if($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = $request->getText($checkbox);    
      
      }elseif($checkbox_without_numbers === 'collection_hidden'){
        $this->collection_hidden_array[$checkbox] = $request->getText($checkbox);
        
      }elseif($checkbox_without_numbers === 'redirect_to_start'){
        $this->redirect_to_start = true; 
        break;        
      }      
    }
    
    if($this->redirect_to_start){
      return false; 
    }
        
    return true; 
  }
  
  /**
   * This function determines if the user has the right permissions. If a valid request was posted, this request is processed. Otherwise, the default page is shown 
   */
  public function execute(){
    
    $out = $this->getOutput();
    $user_object = $this->getUser();    
    
    if(!in_array('ManuscriptEditors',$user_object->getGroups())){
      return $out->addWikiText('collate-nopermission');
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
   * Processes the request when a user has submitted the form
   * 
   * @return type
   */
  private function processRequest(){
  }
  
  /**
   *  This function constructs the $titles_array used by the table, and removes the base url   
   */
  private function constructTitles(){
    
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
    
    $titles_array = !empty($posted_hidden_collection_titles) ? array_merge($this->posted_titles_array,$posted_hidden_collection_titles) : $this->posted_titles_array; 
    
    
    $full_manuscripts_url = $this->full_manuscripts_url; 

    foreach($titles_array as &$full_url){

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

      if(!$title_object->exists()){
        return false; 
      }

      $single_page_text = $this->getSinglePageText($title_object);

      $texts[] = $single_page_text; 
    }
  
    if($this->collection_array){
      //collect all single pages of a collection and merge them together
      foreach($this->collection_array as $collection_name => $json_url_array){

        $url_array = json_decode($json_url_array);

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

        //add the combined texts to $texts
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
    
    //metatable tag
    $raw_text = preg_replace('/<metatable>[^<]+<\/metatable>/i', '', $raw_text);
    
    //del tag
    $raw_text = preg_replace('/<del>[^<]+<\/del>/i', '', $raw_text);

    //note tag
    $raw_text = preg_replace('/<note>[^<]+<\/note>/i', '', $raw_text);
    
    //filter out any other tags, but keep all text in between the tags
    $raw_text = strip_tags($raw_text);
    
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
    
    $stylometric_analysis_wrapper = new stylometricAnalysisWrapper($this->user_name);
    
    $collection_urls = $stylometric_analysis_wrapper->checkForManuscriptCollections();
    
    //check if the total number of collections is less than the minimum
    if(count($collection_urls) < $this->minimum_collections){
      return $out->addWikiText($this->msg('stylometricanalysis-fewcollections'));
    }
    
    //check for each collection if the number of pages is less than the minimum
    foreach($collection_urls as $collection_name => $smaller_url_array){
      if(count($smaller_url_array) < $this->minimum_pages_per_collection){
        return $out->addWikiText($this->msg('stylometricanalysis-fewpages'));
      }
    }
    
    return $this->showDefaultPage($collection_urls, $out);    
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
   * This function constructs the HTML for the default page
   * 
   * @param type $collection_urls
   * @param type $out
   */
  private function showDefaultPage($collection_urls, $out){
    
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('stylometricanalysis-welcome'));
    
    $about_message = $this->msg('stylometricanalysis-about');
    $version_message = $this->msg('stylometricanalysis-version');  
    $software_message = $this->msg('stylometricanalysis-software');
    $lastedit_message = $this->msg('stylometricanalysis-lastedit');
    
    $html  = "<table id='stylometricanalysis-infobox'>";
    $html .= "<tr><th>$about_message</th></tr>";
    $html .= "<tr><td>$version_message</td></tr>";
    $html .= "<tr><td>$software_message <a href= '' target='_blank'>    </a>.</td></tr>";
    $html .= "<tr><td id='stylometricanalysis-td'><small>$lastedit_message</small></td></tr>";
    $html .= "</table>";
    
    $html .= $this->msg('stylometricanalysis-instruction') . '<br>';
        
    //display the error 
    if($this->error_message){
      
     $error_message = $this->error_message;  
     $html .= "<div class = 'error'>$error_message</div>";
    }
        
    $html .= "<form id='stylometricanalysis-form' action='" . $article_url . "Special:StylometricAnalysis' id='stylometricanalysis-form' method='post'>";
      
    $collection_header = $this->msg('stylometricanalysis-collectionheader');

    $html .= "<div id='stylometricanalysis-collection'>";
    $html .= "<h3>$collection_header</h3>";
    $html .= "<ol class ='checkbox_grid'>";

    $a = 0;
    foreach($collection_urls as $collection_name=>$small_url_array){

      //this will be sent when the checkbox is selected
      $json_small_url_array = json_encode($small_url_array['manuscripts_url']);

      //this is to construct the information about the collection which will be displayed to the user
      $manuscript_pages_within_collection = implode(', ',$small_url_array['manuscripts_title']);
      $collection_text = $this->msg('stylometricanalysis-contains') . $manuscript_pages_within_collection . '.';

      //add a checkbox for the collection
      $html .="<li>";
      $html .="<input type='checkbox' name='collection$a' value='$json_small_url_array'>$collection_name";
      $html .="<input type='hidden' name='collection_hidden$a' value='$collection_name'>"; 
      $html .= "<br>";
      $html .= $collection_text; 
      $html .="</li>";
      $html .="<br>";
      $a = ++$a; 
    }
      
    $html .= "</ol>";
    $html .= "</div>";
    
    $word_form_header = $this->msg('stylometricanalysis-wordformheader');
    $placeholder_text = $this->msg('stylometricanalysis-placeholder');
    
    $html .= "<div id='stylometricanlaysis-wordform'>";
    $html .= "<h3>$word_form_header</h3>";
      
    $html .= "<br><br>"; 
      
    $html .= "<textarea rows='4' cols = '50' id='stylometricanalysis-textarea' maxlength='500' placeholder='$placeholder_text'>";
    $html .= "</textarea>";
      
    $submit_hover_message = $this->msg('stylometricanalysis-hover');
    $submit_message = $this->msg('stylometricanalysis-submit');
    
    $html .= "<input type = 'submit' id='stylometricanalysis-submitbutton' title = $submit_hover_message value=$submit_message>";
    
    $html .= "</form>";
        
    $out->addHTML($html);  
  }
}
