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
 * Todo: Perhaps make use of the mediawiki tokens for security
 * 
 * Possible problems: When you have mutliple tabs, and collate multiple times, and go back to the first collation, and save this one, it will save the wrong data....
 * Possible solution: When you save the table in temp_database, send a token to the user (50 digit random number for example), and save this token in the database
 * 
 * Save a time in the database.. check whether the time difference between current and last collation is at least ... 1 hour? If so.. delete all the old collation data from database.
 * Otherwise, keep the data for now. 
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
  private $max_pages_collection; 
   
  //class constructor
	public function __construct(){
    
    global $wgNewManuscriptOptions, $wgMetaTableTag, $wgArticleUrl;  
    
    $this->article_url = $wgArticleUrl; 
    
    //if $minimum_manuscripts, $maximum_manuscripts and $max_pages_collection is changed, remember to change the corresponding text in collate.i18n.php. 
    $this->minimum_manuscripts = 2; 
    $this->maximum_manuscripts = 5; 
    $this->max_pages_collection = 6; 
    
    $this->save_table = false; //default value
    $this->error_message = false; //default value
    $this->metatable_tag = $wgMetaTableTag;
    $this->manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
    $this->redirect_to_start = false;
    $this->posted_titles_array = array();
    $this->collection_array = array();

    include('textConverter.php');
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
        $this->posted_titles_array[$checkbox] = $request->getText($checkbox); 

      }elseif($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = $request->getText($checkbox);    
      
      }elseif($checkbox_without_numbers === 'collection_hidden'){
        $this->collection_hidden_array[$checkbox] = $request->getText($checkbox);
        
      }elseif($checkbox_without_numbers === 'redirect_to_start'){
        $this->redirect_to_start = true; 
        break; 
        
      }elseif($checkbox_without_numbers === 'save_current_table'){
        $this->save_table = true;
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
    
    //check if the user has checked too many boxes
    if(count($this->posted_titles_array)+count($this->collection_array) > $this->maximum_manuscripts){
      return $this->showError('collate-error-manytexts');
    }
    
    $collection_count = 0; 
    
    foreach($this->collection_array as $collection_name => $json_url_array){
      $url_array = json_decode($json_url_array);
      $collection_count += count($url_array);
    }
    
    //check if there are more pages from collections than is allowed
    if($collection_count > $this->max_pages_collection){
      return $this->showError('collate-error-collectionmanytexts');
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
    
    //construct all the titles, used by the table
    $titles_array = $this->constructTitles();
    
    //construct an URL for the new page
    list($main_title, $new_url) = $this->makeURL($titles_array);

    $status = $this->storeTempcollate($titles_array, $main_title, $new_url, $collatex_output);
    
    if(!$status){
      return $this->showError('collate-error-database');
    }
           
    $this->showFirstTable($titles_array, $collatex_output);
  }
  
  /**
   * This function processes the request when the user wants to save the collation table. Preloaded wikitext is retrieved, a new page is made, and the user is redirected to
   * this page
   */
  private function processSaveTable(){
    
    $user_name = $this->user_name; 
    list($titles_array, $new_url, $main_title, $main_title_lowercase, $collatex_output) = $this->getTempcollate();
            
    $local_url = $this->createNewPage($new_url);
    
    if(!$local_url){
      return $this->showError('collate-error-wikipage');
    }
    
    $status = $this->storeCollations($new_url, $main_title, $main_title_lowercase, $titles_array, $collatex_output);
    
    if(!$status){
      return $this->showError('collate-error-database');
    }
                  
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
    $content_new = new wikitextcontent('<!--page created-->');
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
    
     //filter out anything after the metatable tag
//    $tag_position = strpos($raw_text,$this->metatable_tag);
//    $raw_text = substr($raw_text,0,$tag_position);
        
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
    
    list($url_array,$title_array) = $this->getManuscriptTitles();

    if(count($url_array) < $this->minimum_manuscripts){
      return $out->addWikiText($this->msg('collate-fewuploads'));
    }

    $collection_urls = $this->checkForManuscriptCollections(); 
    
    return $this->showDefaultPage($url_array,$title_array,$collection_urls, $out);    
	}
   
  /**
   * This function checks if any uploaded manuscripts are part of a larger collection of manuscripts by retrieving data from the 'manuscripts' table
   * 
   * @param type $collection_urls
   * @return type
   */
  private function checkForManuscriptCollections($collection_urls = array()){
    
    $user_name = $this->user_name; 
    $dbr = wfGetDB(DB_SLAVE);
    
    $conds = array(
       'manuscripts_user = ' . $dbr->addQuotes($user_name),
       'manuscripts_collection != ' . $dbr->addQuotes(""),
       'manuscripts_collection != ' . $dbr->addQuotes("none"),
     ); 
    
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_collection',
      ),
      $conds, //conditions
      __METHOD__,
      array(
      'ORDER BY' => 'manuscripts_collection',
      )
    );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
                    
        //check if the current collection has been added
        if(!isset($collection_urls[$s->manuscripts_collection])){
          $collection_urls[$s->manuscripts_collection] = array(
              'manuscripts_url' => array($s->manuscripts_url),
              'manuscripts_title' => array($s->manuscripts_title),
              );
     
        //if the collection already has been added, append the new manuscripts_url to the current array
        }else{
          end($collection_urls);
          $key = key($collection_urls);
          $collection_urls[$key]['manuscripts_url'][] = $s->manuscripts_url;
          $collection_urls[$key]['manuscripts_title'][] = $s->manuscripts_title;

        }                
      }     
    }
  
    return $collection_urls; 
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
   * This function fetches the data showing which pages have been created by the current user by retrieving this data from the 'manuscripts' table
   * 
   * @return type
   */
  private function getManuscriptTitles($url_array = array(), $title_array = array()){
    
    $dbr = wfGetDB(DB_SLAVE);
    $user_name = $this->user_name;   
        
    //conditions: the user should be the current user
      $conds = array(
      'manuscripts_user = ' . $dbr->addQuotes($user_name),  
      ); 
    
    //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_title',//values
        'manuscripts_url',
        'manuscripts_lowercase_title',
         ),
      $conds, //conditions
      __METHOD__,
      array(
        'ORDER BY' => 'manuscripts_lowercase_title',
      )
      );
        
    if ($res->numRows() > 0){
      //while there are still titles in this query
      while ($s = $res->fetchObject()){
        
        //add titles to the title array and url array     
        $title_array[] = $s->manuscripts_title;
        $url_array[] = $s->manuscripts_url;     
      }     
    }
   
  return array($url_array, $title_array);   
  }
   
  /**
   * This function gets the stored collate values from 'tempcollate'
   */
  private function getTempcollate(){
        
    $dbr = wfGetDB(DB_SLAVE);
    $user_name = $this->user_name; 
    
    $conds =  array(
      'tempcollate_user = ' . $dbr->addQuotes($user_name),  
      ); 
    
    //Database query
    $res = $dbr->select(
      'tempcollate', //from
      array(
        'tempcollate_user',//values
        'tempcollate_titles_array',
        'tempcollate_new_url',
        'tempcollate_main_title',
        'tempcollate_main_title_lowercase',
        'tempcollate_collatex'
         ),
      $conds, //conditions
      __METHOD__ 
      );
        
    if ($res->numRows() === 1){
      $s = $res->fetchObject();
       
      $titles_array = $s->tempcollate_titles_array;
      $new_url = $s->tempcollate_new_url;
      $main_title = $s->tempcollate_main_title;
      $main_title_lowercase = $s->tempcollate_main_title_lowercase; 
      $collatex_output = $s->tempcollate_collatex;
      
      
      return array($titles_array, $new_url, $main_title, $main_title_lowercase, $collatex_output);
    
    }else{

      return false; 
    }
  }
  
  /**
   * Insert the result of the collation into 'tempcollate', which will be used when the user wants to save the current table
   *  
   * @param type $collatex_output
   */
  private function storeTempcollate($titles_array, $main_title, $new_url, $collatex_output){
        
    $titles_array = json_encode($titles_array);
    $main_title_lowercase = strtolower($main_title);

		$dbw = wfGetDB(DB_MASTER);
    
    $insert_values = array( 
			'tempcollate_user'                  => $this->user_name,  
      'tempcollate_titles_array'          => $titles_array,
      'tempcollate_new_url'               => $new_url,
      'tempcollate_main_title'            => $main_title,
      'tempcollate_main_title_lowercase'  => $main_title_lowercase,  
			'tempcollate_collatex'              => $collatex_output
			);
    
    //upsert = INSERT.. ON DUPLICATE KEY UPDATE
		$dbw->upsert(
        'tempcollate', //select table
         $insert_values,
         array('tempcollate_user'), //tempcollate_unique
         $insert_values,
         __METHOD__ 
        );
        
		if ($dbw->affectedRows()){
      //insert succeeded
			return true;
		}else{
		  //return error
	    return false;
		}
  }
  
  /**
   * This function stores the collation data in 'collations' when the user chooses to save the current table
   * 
   * @param type $new_url
   * @param type $main_title
   * @param type $main_title_lowercase
   * @return boolean
   */
  	private function storeCollations($new_url, $main_title, $main_title_lowercase, $titles_array, $collatex_output){
      
    $user_name = $this->user_name; 
      
    $date = date("d-m-Y H:i:s"); 
    
    $main_title_lowercase = strtolower($main_title);
  
		$dbw = wfGetDB(DB_MASTER);
		$dbw->insert('collations', //select table
      array( //insert values
			'collations_user'                 => $user_name,
			'collations_url'                  => $new_url,
      'collations_date'                 => $date,
      'collations_main_title'           => $main_title, 
      'collations_main_title_lowercase' => $main_title_lowercase,
      'collations_titles_array'         => $titles_array,
      'collations_collatex'             => $collatex_output    
			),__METHOD__,
			'IGNORE' );
		if ($dbw->affectedRows()){
      //insert succeeded
			return true;
		}else{
		  //return error
	    return false;
		}
	}
  
  /**
   * This function constructs the HTML collation table, and buttons
   * 
   * @param type $title_array
   * @param type $collatex_output
   */
  private function showFirstTable($title_array,$collatex_output){
    
    $out = $this->getOutput(); 
    
    $article_url = $this->article_url; 
        
    $html = $this->msg('collate-success') . '<br><br>' . $this->msg('collate-tableread');

    $html .= $this->msg('collate-savetable') . '<br><br>'; 
    
    $redirect_hover_message = $this->msg('collate-redirecthover');
    $redirect_message = $this->msg('collate-redirect');
    
    $save_hover_message = $this->msg('collate-savehover');
    $save_message = $this->msg('collate-save');
    
    $html .= "
       <div>
            <form action='" . $article_url . "Special:BeginCollate' method='post'> 
            <input type='submit' style = 'width: 30em; height: 1.5em; cursor: pointer;' name = 'redirect_to_start' title='$redirect_hover_message'  value='$redirect_message'>
            </form><br>
            
            <form action='" . $article_url . "Special:BeginCollate' method='post'> 
            <input type='submit' style = 'width: 30em; height: 1.5em; cursor: pointer;' name = 'save_current_table' title='$save_hover_message' value='$save_message'> 
            </form>
       </div>";
    
    $html .= "
       <script> var at = $collatex_output;</script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/yui-min.js'></script>
       <script src='/w/extensions/collate/specials/javascriptcss/jquery.min.js'></script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/collatex.js'></script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/collatexTwo.js'></script>
       <link rel='stylesheet' type='text/css' href='/w/extensions/collate/specials/javascriptcss/collatex.css'> 
       ";
    
 
    $html .="
     <body onload='loadTable();'>
      <table class='alignment'>"; 
    
    foreach($title_array as $key=>$title){
      $html .=
      "<tr>
       <th>$title</th>
       </tr>";
    }
    
    $html .= "         
    </table>
    <div id='body'>
      <div id='result'>
      </div>
    </div>
    </body>";
    
    $out->addHTML($html);  
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
    
    $html  = "<table class='infobox'";
    $html .= "style='padding:0.3em; float:right; margin-left:15px; margin-bottom:8px; border:1px solid #aa8899; background:#e1e9e1; text-align:center; font-size:95%; line-height:1.5em; width: 28em;'>";
    $html .= "<tr><th style='background:#286819; padding:0.3em; font-size:1.1em;  color: #FFF'>$about_message</th></tr>";
    $html .= "<tr><td>$version_message</td></tr>";
    $html .= "<tr><td>$software_message <a href= 'http://collatex.net' target='_blank'> Collatex Tools 1.5</a>.</td></tr>";
    $html .= "<tr><td style='background:#286819; padding:0.3em; color: #FFF;'><small>$lastedit_message</small></td></tr>";
    $html .= "</table>";
    
    $html .= $this->msg('collate-instruction1') . '<br>';
    
    if(!empty($collection_urls)){
      $html .= $this->msg('collate-instruction2') .  '<br>';
    }
    
    //$html .= $this->msg('<br>');
    
    if($this->error_message){
     $error_message = $this->error_message;
     
     $html .= "<div class = 'error'>$error_message</div>";
    }
    
    $manuscript_message = $this->msg('collate-manuscriptpages');
    
    $html .= "<form action='" . $article_url . "Special:BeginCollate' id='begincollate-form' method='post'>";
    
    $html .= "<div id='begincollate-manuscriptpages'>";
    $html .= "<h3>$manuscript_message</h3>";
    $html .= "<ol class = 'checkbox_grid'>";
    
    //display a checkbox for each manuscript uploaded by this user
    foreach($url_array as $index=>$url){
      
      //get corresponding title
      $title_name = $title_array[$index];
      
      $html .="<li><input type='checkbox' name='text$index' id= 'collate_checkbox' value='$url'>$title_name</li>";
    }
    
    $html .= "</ol>";   
    $html .= "</div>";
       
    //if there are manuscript collections
    if(!empty($collection_urls)){
      
      $collection_message = $this->msg('collate-collections');
            
      $html .= "<div id='begincollate-collections'>";
      $html .= "<h3>$collection_message</h3>";
      $html .= "<ol class ='checkbox_grid'>";

      $a = 0;
      foreach($collection_urls as $collection_name=>$small_url_array){
      
        $json_small_url_array = json_encode($small_url_array['manuscripts_url']);
        
        $manuscript_pages_within_collection = implode(', ',$small_url_array['manuscripts_title']);
        
        $collection_text = $this->msg('collate-contains') . $manuscript_pages_within_collection . '.';
                
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
    }
  
    $html .= "</div>";
    $html .= "<br><br>"; 
    
    $submit_hover_message = $this->msg('collate-hover');
    $submit_message = $this->msg('collate-submit');
    
    $html .= "<input type = 'submit' style = 'width: 30em; height: 1.5em; cursor: pointer;' title = $submit_hover_message value=$submit_message></form>";
        
    $out->addHTML($html);  
  }
}


