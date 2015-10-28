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

class SpecialStylometricAnalysis extends SpecialPage {
  
  public $article_url; 
  
  private $minimum_collections;
  private $maximum_collections; 
  private $minimum_pages_per_collection; 
  private $user_name;  
  private $full_manuscripts_url; 
  private $collection_array;
  private $error_message;
  private $manuscripts_namespace_url;
  private $max_length;
  private $variable_validated;
  private $token_is_ok;
  private $web_root; 
  private $python_path; 
  
  //basic validation variables for stylometric analysis options form
  private $variable_validated_number;
  private $variable_validated_empty; 
  private $variable_validated_max_length;
  
  //user stylometric analysis options 
  private $removenonalpha;
  private $lowercase; 
  
  private $tokenizer;
  private $minimumsize;
  private $maximumsize;
  private $segmentsize;
  private $stepsize;
  private $removepronouns;
  
  private $vectorspace;
  private $featuretype;
  private $ngramsize; 
  private $mfi;
  private $minimumdf;
  private $maximumdf;
   
  //class constructor
  public function __construct(){
    
    global $wgNewManuscriptOptions, $wgArticleUrl, $wgStylometricAnalysisOptions, $wgWebsiteRoot;  
    
    $this->article_url = $wgArticleUrl;
    $this->manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
    $this->minimum_collections = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];  
    $this->maximum_collections = $wgStylometricAnalysisOptions['wgmax_stylometricanalysis_collections']; 
    $this->minimum_pages_per_collection = $wgStylometricAnalysisOptions['minimum_pages_per_collection'];
    $this->python_path = $wgStylometricAnalysisOptions['python_path'];
    
    $this->error_message = false; //default value     
    $this->variable_validated = true; //default value
    $this->variable_validated_number = true;//default value
    $this->variable_validated_empty = true;//default value 
    $this->variable_validated_max_length = true;//default value
    
    $this->collection_array = array();
    
    $this->max_length = 50; 
    
    $this->web_root = $wgWebsiteRoot; 
    
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
      
      //remove the numbers from $checkbox to see if it matches to 'collection'
      $checkbox_without_numbers = trim(str_replace(range(0,9),'',$checkbox));

      if($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = (array)$this->validateInput(json_decode($request->getText($checkbox)));                      
      }elseif($checkbox_without_numbers === 'wpEditToken'){
        $token = $request->getVal('wpEditToken');
        $this->token_is_ok = $this->getUser()->matchEditToken($token);
        break; 
      }     
    }
               
    if($this->token_is_ok === true){
      
      $this->removenonalpha = $this->validateInput($request->getText('wpremovenonalpha'));
      $this->lowercase = $this->validateInput($request->getText('wplowercase'));
      
      $this->tokenizer = $this->validateInput($request->getText('wptokenizer'));
      $this->minimumsize = (int)$this->validateNumber($request->getText('wpminimumsize'));
      $this->maximumsize = (int)$this->validateNumber($request->getText('wpmaximumsize'));
      $this->segmentsize = (int)$this->validateNumber($request->getText('wpsegmentsize'));
      $this->stepsize = (int)$this->validateNumber($request->getText('wpstepsize'));
      $this->removepronouns = $this->validateInput($request->getText('wpremovepronouns'));
      
      $this->vectorspace = $this->validateInput($request->getText('wpvectorspace'));
      $this->featuretype = $this->validateInput($request->getText('wpfeaturetype'));
      
      $this->ngramsize = (int)$this->validateNumber($request->getText('wpngramsize'));
      $this->mfi = (int)$this->validateNumber($request->getText('wpmfi'));
      $this->minimumdf = (int)$this->validateNumber($request->getText('wpminimumdf'));
      $this->maximumdf = (int)$this->validateNumber($request->getText('wpmaximumdf'));
      
 
      $this->collection_array = (array)$this->validateInput(json_decode($request->getText('collection_array')));
      
      foreach($this->collection_array as $index=>&$value){
        $this->collection_array[$index] = (array)$value;
      }
      
      $this->removenonalpha = empty($this->removenonalpha) ? 0 : $this->removenonalpha; 
      $this->lowercase = empty($this->lowercase) ? 0 : $this->lowercase;
      $this->removepronouns = empty($this->removepronouns) ? 0 : $this->removepronouns; 
      
      return true; 
    }
    
    if($this->variable_validated === false || $this->token_is_ok === false){
      return false; 
    }
              
    return true; 
  }
  
  /**
   * This function checks if basic form conditions are met 
   * 
   * @param type $input
   */
  private function validateInput($input){
    
    if(is_array($input) || is_object($input)){
      
      foreach($input as $index => $value){
        $status = $this->validateInput($value);
        
        if(!$status){
          return false; 
        }
      }
      
      return $input; 
    }
    
    //check if all charachters are alphanumeric, or '/' or ':' (in case of url)
    if(!preg_match('/^[a-zA-Z0-9:\/]*$/', $input)){
      $this->variable_validated = false; 
      return false; 
    }
    
    //check for empty variables or unusually long string lengths
    if(empty($input) || strlen($input) > 500){
      $this->variable_validated = false; 
      return false; 
    }
    
    return $input; 
  }
  
  /**
   * This function checks if basic form conditions are met for numbers. Field specific validation is done later 
   */
  private function validateNumber($input){
    
    $max_length = $this->max_length; 
    
    //check if all the input consists of numbers or '.'
    if(!preg_match('/^[0-9.]*$/', $input)){
      $this->variable_validated_number = false; 
      return false; 
    }
    
    //check for empty variables 
    if(empty($input) && $input !== '0'){
      $this->variable_validated_empty = false; 
      return false; 
    }
    
    //check if the input is not longer than $max_length
    if(strlen($input) > $max_length){
      $this->variable_validated_max_length = false; 
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
      return $out->addHTML($this->msg('stylometricanalysis-nopermission'));
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
   * Processes the request when a user has submitted a form
   * 
   * @return type
   */
  private function processRequest(){
    
    $web_root = $this->web_root; 
      
    //Form1: Stylometric Analysis collection selection
    if(!isset($this->token_is_ok)){
    
      if(count($this->collection_array) < $this->minimum_collections){
        return $this->showError('stylometricanalysis-error-fewcollections', 'Form1');
      }

      if(count($this->collection_array) > $this->minimum_collections){
        return $this->showError('stylometricanalysis-error-manycollections', 'Form1');
      }

      return $this->showStylometricAnalysisForm();
    }
    
    //Form2: Stylometric Analysis options form
    if($this->variable_validated_number === false){
      return $this->showError('stylometricanalysis-error-number', 'Form2');
    }
    
    if($this->variable_validated_empty === false){
      return $this->showError('stylometricanalysis-error-empty', 'Form2');
    }
    
    if($this->variable_validated_max_length === false){
      return $this->showError('stylometricanalysis-error-maxlength', 'Form2');
    }
    
    //field specific errors (values that are too high or too low)
      
    $texts = $this->constructTexts();
    
    //if returned false, one of the posted pages did not exist
    if($texts === false){
      wfErrorLog($this->msg('stylometricanalysis-error-notexists') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');   
      return $this->showError('stylometricanalysis-error-notexists', 'Form1');
    }
        
    $config_array = array(
      "'removenonalpha'" => "'$this->removenonalpha'",
      "'lowercase'" => "'$this->lowercase'", 
      "'tokenizer'" => "'$this->tokenizer'",
      "'minimumsize'" => "'$this->minimumsize'",
      "'maximumsize'" => "'$this->maximumsize'",
      "'segmentsize'" => "'$this->segmentsize'",
      "'stepsize'" => "'$this->stepsize'",
      "'removepronouns'" => "'$this->removepronouns'",
      "'vectorspace'" => "'$this->vectorspace'",
      "'featuretype'" => "'$this->featuretype'",
      "'ngramsize'" => "'$this->ngramsize'",
      "'mfi'" => "'$this->mfi'",
      "'minimumdf'" => "'$this->minimumdf'",
      "'maximumdf'" => "'$this->maximumdf'",
      "'texts'" => $texts, 
    );
    
    //$config_array = array_merge($config_array, $texts);
    
    $data = json_encode($config_array);
    $data = escapeshellarg($data);

    $output = system(escapeshellcmd($this->constructCommand() . ' ' . $data));

    $this->getOutput()->addHTML($output); 
               
    //in this screen enable users to select 3 options: only use your words, only use the calculated words, use both.     
    //they can also choose to run a PCA analysis or a clustering analysis     
    //only after clicking clustering analysis or PCA analysis, the texts should be assembled 
  }
  
  /**
   * This function constructs the shell command in order to call PyStyl
   */
  private function constructCommand(){
    
    $python_path = $this->python_path;            
    $dir = dirname( dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'PyStyl' . DIRECTORY_SEPARATOR . 'pystyl' . DIRECTORY_SEPARATOR . 'example.py';  
    //test.py
        
    return $python_path . ' ' . $dir; 
  }
  
  /**
   * This function loops through all the posted collections, and
   * retrieves the text from the corresponding pages 
   * 
   * @return type
   */
  private function constructTexts(){
    
    //in $texts combined collection texts will be stored 
    $texts = array();
    $a = 1; 
  
    if($this->collection_array){
      //for collections, collect all single pages of a collection and merge them together
      foreach($this->collection_array as $collection_index => $url_array){

        $all_texts_for_one_collection = "";

        //go through all urls of a collection
        foreach($url_array as $index => $file_url){
          
          if($index !== 'collection_name'){

            $title_object = Title::newFromText($file_url);

            if(!$title_object->exists()){
              return false; 
            }

            $single_page_text = $this->getSinglePageText($title_object);
            //add $single_page_text to $single_page_texts
            $all_texts_for_one_collection .= $single_page_text; 
          }
        }
        
        $collection_name = isset($url_array['collection_name']) ? $url_array['collection_name'] : 'collection' . $a; 

        //add the combined texts of one collection to $texts
        $texts["'collection" . $a . "'"] = array(
          "'title'" => "'" . $collection_name . "'",
          "'target_name'" => "'" . $collection_name . "'",
          "'text'" => "'" . $all_texts_for_one_collection . "'",
        );
        $a += 1; 
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
    
    $stylometric_analysis_wrapper = new stylometricAnalysisWrapper($this->user_name, $this->minimum_pages_per_collection);   
    $collection_urls = $stylometric_analysis_wrapper->checkForManuscriptCollections();
        
    //check if the total number of collections is less than the minimum
    if(count($collection_urls) < $this->minimum_collections){
                
      $article_url = $this->article_url;
      
      $html = "";
      $html .= $this->msg('stylometricanalysis-fewcollections');    
      $html .= "<p><a class='stylometricanalysis-transparent' href='" . $article_url . "Special:NewManuscript'>Create a new collection</a></p>";
      
      return $out->addHTML($html);     
    }
   
    return $this->showDefaultPage($collection_urls, $out);    
	}
  
  /**
   * This function fetches the correct error message, and redirects to showDefaultPage()
   * 
   * @param type $type
   */
  private function showError($type, $context){
    
    if(!empty($type)){
      $error_message = $this->msg($type);
    }else{
      $error_message = '';
    }
       
    $this->error_message = $error_message;    
    
    if($context === 'Form1'){
      return $this->prepareDefaultPage($this->getOutput());
    }elseif($context === 'Form2'){
      return $this->showStylometricAnalysisForm();
    }    
  }
  
 /**
  * This function adds html used for the stylometricanalysis loader
  * 
  * Source of the gif: http://preloaders.net/en/circular
  */
  private function addStylometricAnalysisLoader(){
    
    //shows after submit has been clicked
    $html  = "<div id='stylometricanalysis-loaderdiv'>";
    $html .= "<img id='stylometricanalysis-loadergif' src='/w/extensions/collate/specials/assets/362.gif' style='width: 64px; height: 64px;"
        . " position: relative; left: 50%;'>"; 
    $html .= "</div>";
    
    return $html; 
  }
  
  /**
   * This function constructs and shows the stylometric analysis form
   */
  private function showStylometricAnalysisForm(){
    
    $article_url = $this->article_url; 
    $collection_array = $this->collection_array;
    $max_length = $this->max_length; 
    $out = $this->getOutput();
    
    $collections_message = $this->constructCollectionsMessage($collection_array); 
    
    $out->setPageTitle($this->msg('stylometricanalysis-options'));
    
    $html = "";
    $html .= "<div id='stylometricanalysis-wrap'>";
    $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Go Back'>Go Back</a>";
    $html .= "<br><br>";
    $html .= $this->msg('stylometricanalysis-chosencollections') . $collections_message . "<br>"; 
    $html .= $this->msg('stylometricanalysis-chosencollection2');   
    $html .= "<br><br>";
    
    //display the error 
    if($this->error_message){     
      $error_message = $this->error_message;  
      $html .= "<div class = 'error'>". $error_message . "</div>";
    }
    
    $html .= "</div>";
    
    $html .= $this->addStylometricAnalysisLoader();
    
    $out->addHTML($html);
    
    $descriptor = array();
    
    $descriptor['removenonalpha'] = array(
      'label' => 'Remove non-alpha',
      'class' => 'HTMLCheckField',
      'section' => 'stylometricanalysis-section-import',
    );
    
    $descriptor['lowercase'] = array(
      'label' => 'Lowercase',
      'class' => 'HTMLCheckField',
      'section' => 'stylometricanalysis-section-import',
    );
    
    $descriptor['tokenizer'] = array(
      'label' => 'Tokenizer',
      'class' => 'HTMLSelectField',
      'options' => array( 
        'Whitespace' => 'Whitespace',
        'Option 2' => 2,
      ),
      'default' => 'Whitespace',
      'section' => 'stylometricanalysis-section-preprocess',
    );
     
    $descriptor['minimumsize'] = array(
      'label' => 'Minimum Size',
      'class' => 'HTMLTextField',
      'default' => 0, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['maximumsize'] = array(
      'label' => 'Maximum Size',
      'class' => 'HTMLTextField',
      'default' => 10000, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['segmentsize'] = array(
      'label' => 'Segment Size',
      'class' => 'HTMLTextField',
      'default' => 0, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['stepsize'] = array(
      'label' => 'Step Size',
      'class' => 'HTMLTextField',
      'default' => 0, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['removepronouns'] = array(
      'label' => 'Remove Pronouns',
      'class' => 'HTMLCheckField',
      'section' => 'stylometricanalysis-section-preprocess',
    );
     
        
    //add field for 'remove these items too'
    
    $descriptor['vectorspace'] = array(
      'label' => 'Vector Space',
      'class' => 'HTMLSelectField',
      'options' => array( 
        'tf'        => 'tf',
        'tf_scaled' => 'tf_scaled',
        'tf_std'    => 'tf_std',
        'tf_idf'    => 'tf_idf',
        'bin'       => 'bin'
      ),
      'default' => 'tf',
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['featuretype'] = array(
      'label' => 'Feature Type',
      'class' => 'HTMLSelectField',
      'options' => array( 
        'word'       => 'word',
        'char'       => 'char',
        'char_wb'    => 'char_wb',
      ),
      'default' => 'word',
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['ngramsize'] = array(
      'label' => 'Ngram Size',
      'class' => 'HTMLTextField',
      'default' => 1, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['mfi'] = array(
      'label' => 'MFI',
      'class' => 'HTMLTextField',
      'default' => 100, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['minimumdf'] = array(
      'class' => 'HTMLTextField',
      'label' => 'Minimum DF',
      'default' => 0.00, 
      'size' => 5,
      'maxlength'=> 5,
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['maximumdf'] = array(
      'class' => 'HTMLTextField',
      'label' => 'Maximum DF',
      'default' => 0.90, 
      'size' => 5, 
      'maxlength'=> 5, 
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $html_form = new HTMLForm($descriptor, $this->getContext());
    $html_form->setSubmitText($this->msg('stylometricanalysis-submit'));
    $html_form->addHiddenField('collection_array', json_encode($collection_array));
    $html_form->setSubmitCallback(array('SpecialStylometricAnalysis', 'processInput'));  
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
   * This function constructs the collections message
   * 
   * @param type $collection_array
   * @return type
   */
  private function constructCollectionsMessage($collection_array){
    
    $collection_name_array = array();
    
    foreach($collection_array as $index=>$small_url_array){
      $collection_name_array[] = $small_url_array['collection_name'];
    }
    
    return implode(', ',$collection_name_array) . ".";
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
    
    $html .= "<p>" . $this->msg('stylometricanalysis-instruction1') . '</p>';
    
    $html .= "<div id='javascript-error'></div>"; 
            
    //display the error 
    if($this->error_message){     
      $error_message = $this->error_message;  
      $html .= "<div class = 'error'>". $error_message . "</div>";
    }
            
    $html .= "<form id='stylometricanalysis-form' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";    
    $html .= "<h3>" . $this->msg('stylometricanalysis-collectionheader') . "</h3>";
       
    $html .= "<table class='stylometricanalysis-table'>";

    $a = 0;
    $html .= "<tr>";
    
    foreach($collection_urls as $collection_name=>$small_url_array){

      if(($a % 4) === 0){  
        $html .= "</tr>";
        $html .= "<tr>";    
      }

      $manuscripts_urls = $small_url_array['manuscripts_url'];
      $manuscripts_urls['collection_name'] = $collection_name; 

      foreach($manuscripts_urls as $index=>&$url){
        $url = htmlspecialchars($url);
      }
      
      //encode the array into json to be able to place it in the checkbox value
      $json_small_url_array = json_encode($manuscripts_urls);       
      $manuscript_pages_within_collection = htmlspecialchars(implode(', ',$small_url_array['manuscripts_title']));   
      $collection_text = $this->msg('stylometricanalysis-contains') . $manuscript_pages_within_collection . '.';

      //add a checkbox for the collection
      $html .="<td>";
      $html .="<input type='checkbox' class='stylometricanalysis-checkbox' name='collection$a' value='$json_small_url_array'>" . htmlspecialchars($collection_name);
      $html .= "<br>";
      $html .= "<span class='stylometricanalysis-span'>" . $collection_text . "</span>"; 
      $html .="</td>";
      $a = ++$a; 
    }

    $html .= "</tr>";
    $html .= "</table>";
  
    $html .= "<br><br>"; 
    
    $submit_hover_message = $this->msg('stylometricanalysis-hover');
    $submit_message = $this->msg('stylometricanalysis-submit');
    
    $html .= "<input type='submit' disabled id='stylometricanalysis-submitbutton' title = $submit_hover_message value=$submit_message>";   
    $html .="</form>";   
    $html .= "<br>";  
    
    $html .= $this->addStylometricAnalysisLoader();
        
    $out->addHTML($html);  
  }
}
