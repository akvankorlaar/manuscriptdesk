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

class SpecialStylometricAnalysis extends ManuscriptDeskBaseSpecials {
  
  public $article_url; 
  
  private $minimum_pages_per_collection; 
  private $user_name;  
  private $error_message;
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
    
  private $min_words_collection;  //min words that should be in a collection. This is checked using str_word_count, but it has to be checked if str_word_count equals the number of tokens.
 
  private $config_array; 
  //removenonalpha : wheter or not to keep alphabetical symbols
  //lowercase : wheter or not to lowercase all charachters
  
  //tokenizer : str, default=None select the `nltk` tokenizer to be used. Currentlysupports: 'whitespace' (split on whitespace) and 'words' (alphabetic series of characters)
  //minimumsize : minimum size of texts (in tokens), to be included in the set of tokenized texts
  //maximumsize : maximum size of texts (in tokens). Longer texts will be truncated to max_size after tokenization
  //segmentsize : segment_size : int, default=0 The size of the segments to be extracted (in tokens). If `segment_size`=0, no segmentation will be applied to the tokenized texts
  //stepsize : The nb of words in between two consecutive segments (in tokens). If `step_size`=zero, non-overlapping segments will be created. Else, segments will partially overlap
  //removepronouns : Whether to remove personal pronouns. If the `corpus.language` is supported, we will load the relevant list from under `pystyl/pronouns`. The pronoun lists are identical to those for 'Stylometry with R'
  
  //vectorspace : Which vector space to use. Must be one of: 'tf', 'tf_scaled', 'tf_std', 'tf_idf', 'bin'
  //featuretype
  //ngramsize : The length of the ngrams to be extracted
  //mfi : The nb of most frequent items (words or ngrams) to extract
  //minimumdf : Proportion of documents in which a feature should minimally occur. Useful to ignore low-frequency features
  //maximumdf : Proportion of documents in which a feature should maximally occur. Useful for 'culling' and ignoring features which don't appear in enough texts
  
  //private $visualization1;
  //private $visualization2; 
 
  public function __construct(){
          
    global $wgStylometricAnalysisOptions, $wgWebsiteRoot;  
    
    $this->minimum_pages_per_collection = $wgStylometricAnalysisOptions['minimum_pages_per_collection'];
    $this->python_path = $wgStylometricAnalysisOptions['python_path'];
    $this->initial_analysis_dir = $wgStylometricAnalysisOptions['initial_analysis_dir'];    
    $this->min_words_collection = $wgStylometricAnalysisOptions['min_words_collection'];
    $this->web_root = $wgWebsiteRoot; 
    
    $user_object = $this->getUser(); 
    $this->user_name = $user_object->getName();
          
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
        return $this->processRequest(); 
      }
      
	  $viewer = new StylometricAnalysisViewer();
	  $viewer->getDefaultPage();
                    
    }catch(Exception $e){
      return $this->handleErrors($e);     
    }
  }
  
  /**
   * Process all requests
   */
  private function processRequest(){
      
    if($this->form1WasPosted()){
	  $this->form = 'Form1';	
      $validator = new ManuscriptDeskBaseValidator();		
      $form_processor = new Form1Processor($this->getRequest(), $validator);
	  $collection_array = $form_processor->processForm1();
	  $viewer = new StylometricAnalysisViewer();
	  return $viewer->showForm2($collection_array);
    }
        
    if($this->form2WasPosted()){
	  $this->form = 'Form2';
	  $this->checkEditToken();
	  $validator = new ManuscriptDeskBaseValidator();		
      $form_processor = new Form2Processor($this->getRequest(), $validator);
	  $this->config_array = $form_processor->processForm2();
	  
      $texts = $this->getPageTexts();
	  	  
      $this->constructFileNames();
      $this->constructFullOutputPath();
      $this->constructFullLinkPath();
	  
      $this->setAdditionalConfigArrayValues($texts); 
      $full_textfilepath = $this->constructFullTextfilePath();
      $this->insertConfigArrayIntoTextfile($full_textfilepath, $config_array);   
      $command = $this->constructShellCommand();  
      $output = $this->callPystyl($command, $full_textfilepath);
      $this->deleteTextfile($full_textfilepath);
      $this->checkPystylOutput($output);
	      
      $database_wrapper = $this->newDatabaseWrapper();
      $database_wrapper->clearOldValues();
      $database_wrapper->storeTempStylometricAnalysis();
      $viewer = new StylometricAnalysisViewer();
	  return $viewer->showResult($data);
    }
        
    return $this->processSaveTable();
  }

  /**
   * Check if form 1 was posted
   */
  private function form1WasPosted(){
    $request = $this->getRequest();  
    if($request->getText('form1Posted') !== ''){
      return true;      
    }
    
    return false; 
  }
  
  /**
   * Check if form 2 was posted
   */
  private function form2WasPosted(){
    $request = $this->getRequest();  
    if($request->getText('form2Posted') !== '' && $this->tokenWasPosted()){
      return true;    
    }
    
    return false; 
  }
  
  /**
   * This function gets the default page
   */
  private function getDefaultPage(){
    $user_collections = $this->getUserCollections();
    $this->checkUserCollections($user_collections);
	$viewer = new StylometricAnalysisViewer();
    $viewer->showForm1($user_collections);   
    return true;   
  }
  
    /**
   * This function loops through all the posted collections, and
   * retrieves the text from the corresponding pages 
   */
  private function getPageTexts(){
    
	$collection_array = $this->config_array['collection_array']; 
	  
    //in $texts combined collection texts will be stored 
    $texts = array();
    $collection_name_array = array();
    $a = 1; 
  
    //for collections, collect all single pages of a collection and merge them together
    foreach($collection_array as $collection_index => $url_array){

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
            
	$config_array = $this->config_array;   
	  
    if($collection_n_words < $config_array['min_words_collection']){
      $this->form = 'Form1';  
      throw new Exception('stylometricanalysis-error-toosmall');  
    }
        
    if($collection_n_words < $config_array['minimumsize']){
      throw new Exception('stylometricanalysis-error-minsize');    
    }
        
    if($collection_n_words < ($config_array['segmentsize']+$config_array['stepsize'])){
      throw new Exception('stylometricanalysis-error-segmentsize');  
    }
        
    if($collection_n_words < $config_array['ngramsize']){
      throw new Exception('stylometricanalysis-error-ngramsize');  
    }
    
    return true; 
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
  private function constructShellCommand(){
    $python_path = $this->python_path;            
    $dir = dirname( dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'PyStyl' . DIRECTORY_SEPARATOR . 'pystyl' . DIRECTORY_SEPARATOR . 'example.py';  
    //test.py      
    return $python_path . ' ' . $dir; 
  }
  
  /**
   * Process when the user wants to save the current analysis results
   */
  private function processSaveTable(){
      
// * Saving table: Repost
// * Save table mode..
// * Transfer data from tempstylometricanalysis -> stylometricanalysis table
// * Delete old entries tempstylometricanalysis table
// * Make new page with appropriate data
// * Redirect User      
  }
  
  /**
   * This function constructs the config array that will be sent to Pystyl
   */ 
  private function setAdditionalConfigArrayValues($texts){	  
	$this->config_array['texts'] = $texts;
	$this->config_array['full_outputpath1'] = $this->full_outputpath1;
	$this->config_array['full_outputpath2'] = $this->full_outputpath2;                
  }
  
  /**
   * This function insert data into the textfile which will be used to call Pystyl
   */
  private function insertConfigArrayIntoTextfile($full_textfilepath, $config_array){
   
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
      throw new Exception('stylometricanalysis-error-noimages');  
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
     
    return new StylometricAnalysisWrapper($this->user_name,0, $time, $this->full_outputpath1, $this->full_outputpath2);      
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
    $stylometric_analysis_wrapper = new StylometricAnalysisWrapper($this->user_name, $this->minimum_pages_per_collection);   
    return $stylometric_analysis_wrapper->checkForManuscriptCollections();      
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
      return $this->showForm2();
    }    
  }
}