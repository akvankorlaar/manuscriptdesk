<?php
/**   
 * Give users the option to save their results
 * 
 * step size cannot be larger than segment size, or larger than any of the collections
 * 
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
  private $max_length;
  private $web_root; 
  private $python_path; 
  private $initial_analysis_dir;
  private $collection_name_array; 
  
  private $form;
  private $file_name1;
  private $file_name2;
  private $base_outputpath;
  private $base_linkpath; 
  private $full_outputpath1;
  private $full_outputpath2; 
  private $full_linkpath1;
  private $full_linkpath2; 
  
  private $min_mfi; //min number of most frequent items to extract. Errors will occur with mfi < 5
  
  //min words that should be in a collection. This is checked using str_word_count, but it has to be checked if str_word_count equals the number of tokens.
  //the reason for this variable is that the analysis does not work when a collection has a very small number of tokens
  private $min_words_collection;
  
  /*
   * User Stylometric Analysis Options. The option descriptions are from PyStyl
   */
  private $removenonalpha; //wheter or not to keep alphabetical symbols
  private $lowercase; //wheter or not to lowercase all charachters
  
  private $tokenizer;   //str, default=None select the `nltk` tokenizer to be used. Currentlysupports: 'whitespace' (split on whitespace) and 'words' (alphabetic series of characters)
  private $minimumsize; //minimum size of texts (in tokens), to be included in the set of tokenized texts
  private $maximumsize; //maximum size of texts (in tokens). Longer texts will be truncated to max_size after tokenization
  private $segmentsize;  //segment_size : int, default=0 The size of the segments to be extracted (in tokens). If `segment_size`=0, no segmentation will be applied to the tokenized texts
  private $stepsize; //The nb of words in between two consecutive segments (in tokens). If `step_size`=zero, non-overlapping segments will be created. Else, segments will partially overlap
  private $removepronouns; // Whether to remove personal pronouns. If the `corpus.language` is supported, we will load the relevant list from under `pystyl/pronouns`. The pronoun lists are identical to those for 'Stylometry with R'
  
  private $vectorspace; //Which vector space to use. Must be one of: 'tf', 'tf_scaled', 'tf_std', 'tf_idf', 'bin'
  private $featuretype;
  private $ngramsize; //The length of the ngrams to be extracted
  private $mfi; //The nb of most frequent items (words or ngrams) to extract
  private $minimumdf; //Proportion of documents in which a feature should minimally occur. Useful to ignore low-frequency features
  private $maximumdf; //Proportion of documents in which a feature should maximally occur. Useful for 'culling' and ignoring features which don't appear in enough texts
  
  private $visualization1;
  private $visualization2; 
 
  public function __construct(){
    
    global $wgNewManuscriptOptions, $wgArticleUrl, $wgStylometricAnalysisOptions, $wgWebsiteRoot;  
    
    $this->article_url = $wgArticleUrl;
    $this->minimum_collections = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];  
    $this->maximum_collections = $wgStylometricAnalysisOptions['wgmax_stylometricanalysis_collections']; 
    $this->minimum_pages_per_collection = $wgStylometricAnalysisOptions['minimum_pages_per_collection'];
    $this->python_path = $wgStylometricAnalysisOptions['python_path'];
    $this->initial_analysis_dir = $wgStylometricAnalysisOptions['initial_analysis_dir'];    
    $this->min_mfi = $wgStylometricAnalysisOptions['min_mfi'];     
    $this->min_words_collection = $wgStylometricAnalysisOptions['min_words_collection'];
    $this->web_root = $wgWebsiteRoot; 

    $this->collection_array = array();     
    $this->max_length = 50;
    
    $user_object = $this->getUser(); 
    $this->user_name = $user_object->getName();
    $this->full_manuscripts_url = $wgNewManuscriptOptions['manuscripts_namespace'] . $this->user_name . '/';
          
    $this->base_outputpath = $this->web_root . '/' . $this->initial_analysis_dir . '/' . $this->user_name;
    $this->base_linkpath = $this->initial_analysis_dir . '/' . $this->user_name; 
    
    parent::__construct('StylometricAnalysis');
  }
  
  /**
   * Main entry point for the page
   */
  public function execute(){
      
    try{  
      $this->checkPermission();
      
      if($this->requestWasPosted()){
        if(!$this->tokenWasPosted()){
           return $this->processForm1(); 
        }
        
        return $this->processForm2();
      }
      
      $this->getDefaultPage();
                    
    }catch(Exception $e){
      $this->handleErrors($e);     
      return true; 
    }
  }
  
  /**
   * This function processes form 1
   */
  private function processForm1(){      
    $this->form = 'Form1';
    $this->loadForm1();
    $this->checkForm1();
    $this->showStylometricAnalysisForm();
    return true;       
  }
   
  /**
   * This function processes form 2
   */
  private function processForm2(){
    $this->form = 'Form2';  
    $this->checkEditToken();
    $this->loadForm2();
    $this->checkForm2();
    
    $texts = $this->getPageTexts();
    $this->constructFileNames();
    $this->constructFullOutputPath();
    $this->constructFullLinkPath();
    
    $config_array = $this->constructConfigArray($texts); 
    $full_textfilepath = $this->constructFullTextfilePath();
    $this->insertIntoTextfile($full_textfilepath, $config_array);   
    $command = $this->constructCommand();  
    $output = $this->callPystyl($command, $full_textfilepath);
    $this->deleteTextfile($full_textfilepath);
    $this->checkPystylOutput($output);
    $this->showResult($output);
    
    $database_wrapper = $this->newDatabaseWrapper();
    $database_wrapper->clearOldValues();
    $database_wrapper->storeTempStylometricAnalysis();
    
    return true;      
  }
  
  /**
   * This function gets the default page
   */
  private function getDefaultPage(){
    $user_collections = $this->getUserCollections();
    $this->checkUserCollections($user_collections);
    $this->showDefaultPage($user_collections);   
    return true;   
  }
  
  /**
   * This function checks if the edit token was posted
   */
  private function tokenWasPosted(){ 
    $edit_token = $this->getEditToken(); 
    
    if($edit_token === ''){
      return false;    
    }
    
    return true;
  }
  
 /**
  * This function gets the edit token
  */
  private function getEditToken(){       
    $request = $this->getRequest();
    return $request->getText('wpEditToken');  
  }
  
  /**
   * This function checks the edit token
   */
  private function checkEditToken(){
    $edit_token = $this->getEditToken();   
    if($this->getUser()->matchEditToken($edit_token) === false){ 
      $this->form = 'Form1';  
      throw new Exception('stylometricanalysis-error-edittoken');
    }
    
    return true; 
  }
  
  /**
   * This function loads the variables in Form 1
   */
  private function loadForm1(){
      
    $request = $this->getRequest();  
    $posted_names = $request->getValueNames();  
     
    //identify the button pressed
    foreach($posted_names as $key=>$checkbox){  
      //remove the numbers from $checkbox to see if it matches to 'collection'
      $checkbox_without_numbers = trim(str_replace(range(0,9),'',$checkbox));

      if($checkbox_without_numbers === 'collection'){
        $this->collection_array[$checkbox] = (array)$this->validateString(json_decode($request->getText($checkbox)));                      
      }   
    }
     
    return true;   
  }
  
  /**
   * This function checks form 1
   */
  private function checkForm1(){
      
    if(count($this->collection_array) < $this->minimum_collections){        
      throw new Exception('stylometricanalysis-error-fewcollections');   
    }

    if(count($this->collection_array) > $this->maximum_collections){
      throw new Exception('stylometricanalysis-error-manycollections');   
    }
    
    return true; 
  }
  
  /**
   * This function loads the variables in Form 2
   */
  private function loadForm2(){
      
    $request = $this->getRequest();  
                               
    $this->removenonalpha = $request->getText('wpremovenonalpha');
    $this->lowercase = $request->getText('wplowercase');

    $this->tokenizer = $this->validateString($request->getText('wptokenizer'));
    $this->minimumsize = (int)$this->validateNumber($request->getText('wpminimumsize'));
    $this->maximumsize = (int)$this->validateNumber($request->getText('wpmaximumsize'));
    $this->segmentsize = (int)$this->validateNumber($request->getText('wpsegmentsize'));
    $this->stepsize = (int)$this->validateNumber($request->getText('wpstepsize'));
    $this->removepronouns = $request->getText('wpremovepronouns');

    $this->vectorspace = $this->validateString($request->getText('wpvectorspace'));
    $this->featuretype = $this->validateString($request->getText('wpfeaturetype'));

    $this->ngramsize = (int)$this->validateNumber($request->getText('wpngramsize'));
    $this->mfi = (int)$this->validateNumber($request->getText('wpmfi'));
    $this->minimumdf = floatval($this->validateNumber($request->getText('wpminimumdf')));
    $this->maximumdf = floatval($this->validateNumber($request->getText('wpmaximumdf')));

    $this->visualization1 = $this->validateString($request->getText('wpvisualization1'));
    $this->visualization2 = $this->validateString($request->getText('wpvisualization2'));

    $this->collection_array = (array)$this->validateString(json_decode($request->getText('collection_array')));

    foreach($this->collection_array as $index=>&$value){
      //cast everything in collection_array to an array
      $this->collection_array[$index] = (array)$value;
    }

    $this->removenonalpha = empty($this->removenonalpha) ? 0 : 1; 
    $this->lowercase = empty($this->lowercase) ? 0 : 1;
    $this->removepronouns = empty($this->removepronouns) ? 0 : 1;
        
    return true; 
  }
  
  /**
   * This function checks form 2 
   */
  private function checkForm2(){
              
    if($this->minimumsize >= $this->maximumsize){
      throw new Exception('stylometricanalysis-error-minmax');   
    }
    
    if($this->stepsize > $this->segmentsize){
      throw new Exception('stylometricanalysis-error-stepsizesegmentsize');   
    }
    
    if($this->mfi < $this->min_mfi){
      throw new Exception('stylometricanalysis-error-mfi');   
    } 
  }
  
  /**
   * This function validates strings inside an array or an object 
   */
  private function validateString($input){
    
    if(is_array($input) || is_object($input)){
      
      foreach($input as $index => $value){
        $status = $this->validateString($value);
      }
      
      return $input; 
    }
    
    //check if all charachters are alphanumeric, or '/' or ':' (in case of url)
    if(!preg_match('/^[a-zA-Z0-9:\/]*$/', $input)){
      throw new Exception('validation-charachters');
    }
    
    //check for empty variables or unusually long string lengths
    if(empty($input) || strlen($input) > 500){
      throw new Exception('validation-charlength');
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
      throw new Exception('stylometricanalysis-error-number');  
    }
    
    if(empty($input) && $input !== '0'){
      throw new Exception('stylometricanalysis-error-empty');  
    }
    
    if(strlen($input) > $max_length){
      throw new Exception('stylometricanalysis-error-maxlength');  
    }
    
    return $input; 
  }
  
  /**
   * This function handles errors
   */
  private function handleErrors($e){
      
    $error_message = $this->error_message = $e->getMessage();   
   
    if($error_message === 'stylometricanalysis-nopermission'){
      return $out->addHTML($this->msg('stylometricanalysis-nopermission')); 
    }
    
    if($error_message === 'stylometricanalysis-error-fewcollections'){
        
      $article_url = $this->article_url;
      
      $html = "";
      $html .= $this->msg('stylometricanalysis-fewcollections');    
      $html .= "<p><a class='stylometricanalysis-transparent' href='" . $article_url . "Special:NewManuscript'>Create a new collection</a></p>";
      
      return $out->addHTML($html);     
    }
    
    if($this->form === 'Form1'){
      $this->getDefaultpage();
      return true; 
      
    }elseif($this->form === 'Form2'){
        
     //show form 2....
      return $this->showStylometricAnalysisForm();
    }    
  }
  
  /**
   * This function checks if the user has the appropriate permissions
   */
  private function checkPermission(){
    $out = $this->getOutput();
    $user_object = $out->getUser();
    //user does not have permission
    if(!in_array('sysop',$user_object->getGroups())){
      throw new Exception('stylometricanalysis-nopermission');
    }
    
    return true; 
  }
  
  /**
   * This function checks if a request was posted
   */
  private function requestWasPosted(){
      
    $request = $this->getRequest();
        
    if(!$request->wasPosted()){
      return false;   
    }  
    
    return true;   
  }
  
  /**
   * This function constructs the config array that will be sent to Pystyl
   */ 
  private function constructConfigArray($texts){
                
    //to be able to send array data to python via the command line, strings must be double quoted, and integers must be single quoted
    $config_array = array(
      "removenonalpha" => $this->removenonalpha,
      "lowercase" => $this->lowercase, 
      "tokenizer" => $this->tokenizer,
      "minimumsize" => $this->minimumsize,
      "maximumsize" => $this->maximumsize,
      "segmentsize" => $this->segmentsize,
      "stepsize" => $this->stepsize,
      "removepronouns" => $this->removepronouns,
      "vectorspace" => $this->vectorspace, 
      "featuretype" => $this->featuretype,
      "ngramsize" => $this->ngramsize, 
      "mfi" => $this->mfi, 
      "minimumdf" => $this->minimumdf, 
      "maximumdf" => $this->maximumdf,
      "base_outputpath" => $this->base_outputpath,
      "full_outputpath1" => $this->full_outputpath1,
      "full_outputpath2" => $this->full_outputpath2,
      "visualization1" => $this->visualization1,
      "visualization2" => $this->visualization2,
      "texts" => $texts, 
    );
        
    return $config_array; 
  }
  
  /**
   * This function insert data into the textfile which will be used to call Pystyl
   */
  private function insertIntoTextfile($full_textfilepath, $config_array){
   
    if(is_file($full_textfilepath)){
      throw new Exception('stylometricanalysis-error-textfile');
      //bad error, should be reported.. 
    }
      
    $textfile = fopen($full_textfilepath, 'w');
    fwrite($textfile, json_encode($config_array));
    fclose($textfile);
    
    if(!is_file($full_textfilepath)){
      throw new Exception('stylometricanalysis-error-textfile');    
    }
    
    return true; 
  }
  
  /**
   * This function deletes a textfile
   */
  private function deleteTextfile($full_textfilepath){
    
    if(!is_file($full_textfilepath)){
      throw new Exception('stylometricanalysis-error-textfiledelete');
    }
    
    unlink($full_textfilepath);
    
    if(is_file($full_textfilepath)){
      throw new Exception('stylometricanalysis-error-textfiledelete');  
    }
  }
  
  /**
   * This function constructs a temporary textfile in which the data for the analysis will be placed later on. Initially, this was done through the command line,
   * but due to some instabilities, this approach is chosen. 
   */
  private function constructFullTextfilePath(){
    return $this->base_outputpath . '/' . 'temptextfile.txt';  
  }
   
  /**
   * This function calls Pystyl through the command line
   */
  private function callPystyl($command, $full_textfilepath){  
    $full_textfilepath = "'$full_textfilepath'";
    return system(escapeshellcmd($command . ' ' . $full_textfilepath));
  }
  
  /**
   * This function checks Pystyl output
   */
  private function checkPystylOutput($output){
      
    //something went wrong when importing data into PyStyl
    if (strpos($output, 'stylometricanalysis-error-import') !== false){
      throw new Exception('stylometricanalysis-error-import');   
    }
    
    //the path already exists
    if (strpos($output, 'stylometricanalysis-error-path') !== false){
      throw new Exception('stylometricanalysis-error-path');   
    }
    
    //something went wrong when doing the analysis in PyStyl
    if (strpos($output, 'stylometricanalysis-error-analysis') !== false){
      throw new Exception('stylometricanalysis-error-analysis');   
    }
    
    if(!is_file($this->full_outputpath1) || !is_file($this->full_outputpath2)){
      throw new Exception('stylometricanalysis-error-outputpath');  
    }
      
    return true;         
  }
  
  /**
   * This function creates a new database wrapper
   */
  private function newDatabaseWrapper(){
      
    if(!isset($this->full_outputpath1) || !isset($this->full_outputpath2)){
      throw new Exception('stylometricanalysis-error-pathsnotset');
    }  
                
    //time format (Unix Timestamp). This timestamp is used to see how old values are
    $time = idate('U');
     
    return new stylometricAnalysisWrapper($this->user_name,0, $time, $this->full_outputpath1, $this->full_outputpath2);      
  }
  
  /**
   * This function shows the output page after the stylometric analysis has completed
   */
  private function showResult($output){
          
    $out = $this->getOutput();
    $article_url = $this->article_url;
    $full_outputpath1 = $this->full_outputpath1;
    $full_outputpath2 = $this->full_outputpath2; 
    $full_linkpath1 = $this->full_linkpath1;
    $full_linkpath2 = $this->full_linkpath2;
    
    $out->setPageTitle($this->msg('stylometricanalysis-output'));
        
    $html = "";
        
    $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Perform New Analysis'>Perform New Analysis</a>";

    //save current analysis button
    
    $html .= "<div style='display:block;'>";
    
    $html .= "<div id='visualization-wrap1'>";
    $html .= "<h2>Analysis One </h2>";
    $html .= "<p>Information about the plot</p>";
    $html .= "<img src='" . $full_linkpath1 . "' alt='Visualization1' height='650' width='650'>";  
    $html .= "</div>";
    
    $html .= "<div id='visualization-wrap2'>";
    $html .= "<h2>Analysis Two </h2>";
    $html .= "<p>Information about the plot</p>";
    $html .= "<img src='" . $full_linkpath2 . "' alt='Visualization2' height='650' width='650'>";  
    $html .= "</div>"; 
    
    $html .= "</div>";
    
    $html .= "<div id='visualization-wrap3'>";    
    $html .= "<h2>Analysis Variables</h2><br>";
    $html .= "Remove non-alpha:" . $this->removenonalpha . "<br>";
    $html .= "Lowercase:" . $this->lowercase . "<br>";
    $html .= "Tokenizer:" . $this->tokenizer . "<br>";
    $html .= "Minimum Size:" . $this->minimumsize . "<br>";
    $html .= "Maximum Size:" . $this->maximumsize . "<br>"; 
    $html .= "Segment Size:" . $this->segmentsize . "<br>";
    $html .= "Step Size:" . $this->stepsize . "<br>";
    $html .= "Remove Pronouns:" . $this->removepronouns . "<br>";
    $html .= "Vectorspace:" . $this->vectorspace . "<br>";
    $html .= "Featuretype:" . $this->featuretype . "<br>";
    $html .= "Ngram Size:" . $this->ngramsize . "<br>";
    $html .= "MFI:" . $this->mfi . "<br>";
    $html .= "Minimum DF:" . $this->minimumdf . "<br>";
    $html .= "Maximum DF:" . $this->maximumdf;
    $html .= "</div>";
    
    $html .= "This is the output of Pystyl: $output";
    
    return $out->addHTML($html);
  }
  
  /**
   * This function constructs the file names
   */
  private function constructFileNames(){
      
    if(!isset($this->collection_name_array)){
      $this->form = 'Form1';  
      throw new Exception('stylometricanalysis-error-collectionnamearray');    
    }  
       
    $imploded_collection_name_array = implode('',$this->collection_name_array);             
    $year_month_day = date('Ymd');   
    $hours_minutes_seconds = date('his');
    
    $this->file_name1 = $imploded_collection_name_array . $year_month_day . $hours_minutes_seconds . '.jpg';
    $this->file_name2 = $imploded_collection_name_array . $year_month_day . $hours_minutes_seconds . 2 . '.jpg'; 
    
    return true; 
  }
  
  /**
   * This function constructs the output paths for the output images
   */ 
  private function constructFullOutputPath(){
    $this->full_outputpath1 = $this->base_outputpath . '/' . $this->file_name1;
    $this->full_outputpath2 = $this->base_outputpath . '/' . $this->file_name2;
        
    if(is_file($this->full_outputpath1) || is_file($this->full_outputpath2)){
      throw new Exception('stylometricanalysis-error-outputpath');   
    } 
    
    return true; 
  }
  
  /**
   * This function constructs the full link paths for the output images
   */
  private function constructFullLinkPath(){    
    $this->full_linkpath1 = '/' . $this->base_linkpath . '/' . $this->file_name1;
    $this->full_linkpath2 = '/' . $this->base_linkpath . '/' . $this->file_name2; 
    return true; 
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
   */
  private function getPageTexts(){
    
    //in $texts combined collection texts will be stored 
    $texts = array();
    $collection_name_array = array();
    $a = 1; 
  
    //for collections, collect all single pages of a collection and merge them together
    foreach($this->collection_array as $collection_index => $url_array){

      $all_texts_for_one_collection = "";

      //go through all urls of a collection
      foreach($url_array as $index => $file_url){
          
        if($index === 'collection_name'){
          $collection_name_array[] = $url_array['collection_name'];
        }else{
            
          $title_object = Title::newFromText($file_url);

          if(!$title_object->exists()){
            wfErrorLog($this->msg('stylometricanalysis-error-notexists') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log'); 
            $this->form = 'Form1';
            throw new Exception('stylometricanalysis-error-notexists');    
          }

          $single_page_text = $this->getSinglePageText($title_object);
          //add $single_page_text to $single_page_texts
          $all_texts_for_one_collection .= $single_page_text; 
        }
      }
        
      $collection_n_words = str_word_count($all_texts_for_one_collection);     
      $this->checkForCollectionErrors($collection_n_words);
                        
      $collection_name = isset($url_array['collection_name']) ? $url_array['collection_name'] : 'collection' . $a; 

      //add the combined texts of one collection to $texts
      $texts["collection" . $a] = array(
        "title" => "$collection_name",
        "target_name" => "$collection_name",
        "text" => "$all_texts_for_one_collection",
      );
      
      $a += 1; 
    }
    
    $this->collection_name_array = $collection_name_array;       
     
    return $texts; 
  }
  
  /**
   * This function checks for collection errors
   */  
  private function checkForCollectionErrors($collection_n_words){
            
    if($collection_n_words < $this->min_words_collection){
      $this->form = 'Form1';  
      throw new Exception('stylometricanalysis-error-toosmall');  
    }
        
    if($collection_n_words < $this->minimumsize){
      throw new Exception('stylometricanalysis-error-minsize');    
    }
        
    if($collection_n_words < ($this->segmentsize+$this->stepsize)){
      throw new Exception('stylometricanalysis-error-segmentsize');  
    }
        
    if($collection_n_words < $this->ngramsize){
      throw new Exception('stylometricanalysis-error-ngramsize');  
    }
    
    return true; 
  }
  
  /**
   * This function retrieves the wiki text from a page
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
   * This function checks if the user collections are less than the minimum
   * 
   * @param type $user_collections
   * @return boolean
   * @throws Exception
   */
  private function checkUserCollections($user_collections){
    if(count($user_collections) < $this->minimum_collections){
      throw new Exception ('stylometricanalysis-error-fewcollections');                     
    }
    
    return true; 
  }
  
  /**
   * This function gets the user collections
   */
  private function getUserCollections(){  
    $out = $this->getOutput();  
    $stylometric_analysis_wrapper = new stylometricAnalysisWrapper($this->user_name, $this->minimum_pages_per_collection);   
    return $stylometric_analysis_wrapper->checkForManuscriptCollections();      
  }
    
 /**
  * This function adds html used for the stylometricanalysis loader
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
        'Whitespace' => 'whitespace',
        'Words' => 'words',
      ),
      'default' => 'whitespace',
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
    
    $descriptor['visualization1'] = array(
      'label' => 'Visualization1',
      'class' => 'HTMLSelectField',
      'options' => array( 
         'Hierarchical Clustering Dendrogram'  => 'dendrogram',
         'PCA Scatterplot' => 'pcascatterplot',
         'TNSE Scatterplot' => 'tnsescatterplot',
         'Distance Matrix Clustering' => 'distancematrix',
         'Hierarchical Clustering' => 'hierarchicalclustering',
         'Variability Based Neighbour Clustering' => 'neighbourclustering',
      ),
      'default' => 'dendrogram',
      'section' => 'stylometricanalysis-section-visualization',
    );
    
    $descriptor['visualization2'] = array(
      'label' => 'Visualization2',
      'class' => 'HTMLSelectField',
      'options' => array( 
         'Hierarchical Clustering Dendrogram'  => 'dendrogram',
         'PCA Scatterplot' => 'pcascatterplot',
         'TNSE Scatterplot' => 'tnsescatterplot',
         'Distance Matrix Clustering' => 'distancematrix',
         'Variability Based Neighbour Clustering' => 'neighbourclustering',
      ),
      'default' => 'dendrogram',
      'section' => 'stylometricanalysis-section-visualization',
    );
    
    $html_form = new HTMLForm($descriptor, $this->getContext());
    $html_form->setSubmitText($this->msg('stylometricanalysis-submit'));
    $html_form->addHiddenField('collection_array', json_encode($collection_array));
    $html_form->setSubmitCallback(array('SpecialStylometricAnalysis', 'processInput'));  
    $html_form->show();
  }
  
    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error. 
     */
  static function processInput($form_data){ 
    return false; 
  }
  
  /**
   * This function constructs the collections message
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
   */
  private function showDefaultPage($user_collections){
      
    $out = $this->getOutput();   
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
    
    foreach($user_collections as $collection_name=>$small_url_array){

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