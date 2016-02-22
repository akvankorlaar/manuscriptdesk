<?php
/**  
 * This file is part of the newManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 * 
 * This file incorporates work covered by the following copyright and
 * permission notice: 
 * 
 * Copyright (C) 2013 Richard Davis
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author Richard Davis <r.davis@ulcc.ac.uk>
 * @author Ben Parish <b.parish@ulcc.ac.uk>
 * @copyright 2013 Richard Davis
 */

class newManuscriptHooks {
  
/**
 * This is the newManuscriptHooks class for the NewManuscript extension. Various aspects relating to interacting with 
 * the manuscript page (and other special pages in the extension)are arranged here, 
 * such as loading the zoomviewer, loading the metatable, adding CSS modules, loading the link to the original image, 
 * making sure a manuscript page can be deleted only by the user that has uploaded it (unless the user is a sysop), and preventing users from making
 * normal wiki pages on NS_MANUSCRIPTS (the manuscripts namespace identified by 'manuscripts:' in the URL)
 */
      
  private $new_manuscript_wrapper;
  
  private $title_options_site_name;
  private $images_root_dir;
  private $mediawiki_dir;
  private $page_title;
  private $page_title_with_namespace;
  private $namespace; 
  private $lang;
  private $viewer_type;
  private $user_fromurl;
  private $filename_fromurl;
  private $document_root; 
  private $manuscript_url_count_size;
  private $original_images_dir;
  private $allowed_file_extensions; 
  private $zoomimage_check_before_delete;
  private $original_image_check_before_delete;
  private $max_charachters_manuscript; 
  private $view;
    
  //class constructor
  public function __construct(){
             
     //initialise the newManuscriptWrapper class to be able to do database calls 
    $this->new_manuscript_wrapper = new newManuscriptWrapper();    
  }
   
 /**
  * Assign globals to properties
  * Creates default values when these have not been set
  */
  private function assignGlobalsToProperties(){

    global $wgLang,$wgScriptPath,$wgOut,$wgNewManuscriptOptions,$wgWebsiteRoot;
    
    $this->manuscript_url_count_size = $wgNewManuscriptOptions['url_count_size'];
    $this->images_root_dir = $wgNewManuscriptOptions['zoomimages_root_dir'];
    $this->original_images_dir = $wgNewManuscriptOptions['original_images_dir'];
    $this->page_title = strip_tags($wgOut->getTitle()->getPartialURL());
    $this->page_title_with_namespace = strip_tags($wgOut->getTitle()->getPrefixedURL());
    $this->namespace = $wgOut->getTitle()->getNamespace();  
    $this->document_root = $wgWebsiteRoot; 
    
    $this->title_options_site_name = 'Manuscript Desk';   
    $this->mediawiki_dir  = $wgScriptPath;
    $this->lang = $wgLang->getCode();
    
    $this->allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];
    $this->max_charachters_manuscript = $wgNewManuscriptOptions['max_charachters_manuscript'];
    
    $this->zoomimage_check_before_delete = false;
    $this->original_image_check_before_delete = false;      
        
    $this->view = false;
    
    return true;
  }
  
  /**
   * editPageShowEditFormFields hook
   * 
   * This function loads the zoomviewer if the editor is in edit mode. 
   */
  public function onEditPageShowEditFormInitial(EditPage $editPage, OutputPage &$output){

    //submit action will only be true in case the user tries to save a page with too many charachters (see '$this->max_charachters_manuscript')
    if( isset($_GET['action']) && $_GET[ 'action' ] !== 'edit' && $_GET['action'] !== 'submit'){
      return true; 
    }
			
    $this->assignGlobalsToProperties();
    
    if(!$this->urlValid()){
      return true;   
    }
    
    $html = $this->formatIframeHTML(); 
    $output->addHTML($html);                
    $output->addModuleStyles('ext.zoomviewer'); 

    return true;
  }
  
  /**
   * This function loads the zoomviewer if the page on which it lands is a manuscript,
   * and if the url is valid.     
   * 
   * @global type $wgOut
   * @param type $output
   * @param type $article
   * @param type $title
   * @param type $user
   * @param type $request
   * @param type $wiki
   * @return boolean
   */
  public function onMediaWikiPerformAction($output,$article,$title,$user,$request,$wiki){
         
    if($wiki->getAction($request) !== 'view' ){
      return true; 
    }            
    
    $this->assignGlobalsToProperties();
    
    if(!$this->urlValid()){
      return true;    
    }
    
    $user_name = $user->getName();
    $user_fromurl = $this->user_fromurl; 
    
    $html = "";    
    $collection_title = $this->new_manuscript_wrapper->getCollection($this->page_title_with_namespace);
    
    if($collection_title !== null){
      $html .= '<h2>' . htmlspecialchars($collection_title) . '</h2><br>';
    }
    
    $html .= "<table id='link-wrap'>";
    $html .= "<tr>";
    
    $html .= $this->getOriginalImageLink();
       
    if($collection_title !== null){
      
      //only show the edit collection metadata link to the owner of the collection
      if($user_name === $user_fromurl){
        $html .= $this->getLinkToEditCollection($collection_title);
      }
      
      $html .= $this->getPreviousNextPageLinks($collection_title);
    }
    
    $html .= "</tr>";
    $html .= "</table>";
      
    $html .= $this->formatIframeHTML();
    $output->addHTML($html);   
    $output->addModuleStyles('ext.zoomviewer'); 
    
    $this->view = true; 
        
    return true;
  }
  
  /**
   * This function gets the link to edit the current collection
   */
  private function getLinkToEditCollection($collection_title){
    
    global $wgArticleUrl;
    
    $article_url = $wgArticleUrl; 
    $page_title_with_namespace = $this->page_title_with_namespace;
    
    $html = "";
    $html .= '<form class="manuscriptpage-form" action="' . $article_url . 'Special:UserPage" method="post">';
    $html .= "<input type='hidden' name='linkcollection' value='" . $collection_title . "'>";
    $html .= "<input type='hidden' name='linkback' value='" . $page_title_with_namespace . "'>";
    $html .= "<td><input class='button-transparent' type='submit' name='editlink' value='Edit Collection Metadata'></td>";
    $html .= "</form>";
    
    return $html;
  }
  
  /**
   * This function gets the links to the previous and the next page of the collection, if they exist 
   */
  private function getPreviousNextPageLinks($collection_title){
    
    global $wgArticleUrl; 
    
    $page_title_with_namespace = $this->page_title_with_namespace;
    $article_url = $wgArticleUrl; 
    
    $dbr = wfGetDB(DB_SLAVE);
        
     //Database query
    $res = $dbr->select(
      'manuscripts', //from
      array(
        'manuscripts_url', //values
        'manuscripts_lowercase_title', 
      ),
      array(
       'manuscripts_collection = ' . $dbr->addQuotes($collection_title),//conditions
      ), 
      __METHOD__,
      array(
      'ORDER BY' => 'manuscripts_lowercase_title',
      )
    );
    
    $no_previous_page = false; 
    
    while ($s = $res->fetchObject()){
      
      //once the current page has been found in the database
      if($s->manuscripts_url === $page_title_with_namespace){
        
        //set the last entry to $previous_page_url, if it exists
        if(isset($previous_url)){
          $previous_page_url = $previous_url;
          continue; 
        }
        
        $no_previous_page = true;
        continue; 
      }
      
      //once $previous_page_url has been sete, or if there is $no_previous_page, set the $next_page_url
      if(isset($previous_page_url) || $no_previous_page === true){
        $next_page_url = $s->manuscripts_url; 
        break;
        
      //otherwise, the current page has not been found yet  
      }else{    
        $previous_url = $s->manuscripts_url;
      }
    }
    
    $html = "";  
    $html .= "<td>";
    
    if(isset($previous_page_url)){
      $html .= "<a href='" . $article_url . htmlspecialchars($previous_page_url) . "' class='link-transparent' title='Go to Previous Page'>Go to Previous Page</a>";
    }
    
    if(isset($previous_page_url) && isset($next_page_url)){
      $html .= "<br>";
    }
              
    if(isset($next_page_url)){
      $html .= "<a href='" . $article_url . htmlspecialchars($next_page_url) . "' class='link-transparent' title='Go to Next Page'>Go to Next Page</a>";
    }
    
    $html .= "</td>";
    
    return $html; 
  }
  
  /**
   * This function returns the link to the original image
   */
  private function getOriginalImageLink(){
    
    $partial_original_image_path = $this->constructOriginalImagePath();
    
    $original_image_path = $this->document_root . $partial_original_image_path; 
    
    if(!is_dir($original_image_path)){
      return "<b>" . $this->getMessage('newmanuscripthooks-errorimage') . "</b>";
    }
    
    $file_scan = scandir($original_image_path);    
    $image_file = isset($file_scan[2])? $file_scan[2] : "";
    
    if($image_file === ""){
      return "<b>" . $this->getMessage('newmanuscripthooks-errorimage') . "</b>";
    }
     
    $full_original_image_path = $original_image_path . $image_file; 
    
    if(!$this->isImage($full_original_image_path)){
      return "<b>" . $this->getMessage('newmanuscripthooks-errorimage') . "</b>";
    }
    
    $link_original_image_path = $partial_original_image_path . $image_file; 
    
    return "<td><a class='link-transparent' href='$link_original_image_path' target='_blank'>" . $this->getMessage('newmanuscripthooks-originalimage') . "</a></td>";   
  }
  
  /**
   * Construct the full path of the original image
   */
  private function constructOriginalImagePath(){
    
    $original_images_dir = $this->original_images_dir;
    $user_fromurl = $this->user_fromurl;
    $filename_fromurl = $this->filename_fromurl;
    
    $original_image_file_path = '/' . $original_images_dir . '/' . $user_fromurl . '/' . $filename_fromurl . '/';
    
    return $original_image_file_path;
  }
  
  /**
   * This function checks if the file is an image. This has been done earlier and more thouroughly when uploading, but these checks are just to make sure
   */
  private function isImage($file){
         
    if(null !== pathinfo($file,PATHINFO_EXTENSION)){
      $extension = pathinfo($file,PATHINFO_EXTENSION);
    }else{
      $extension = null; 
    }
       
    if(!in_array($extension, $this->allowed_file_extensions) || getimagesize($file) === false){      
      return false;
    }
    
    return true; 
  }
  
  /**
   * This function checks if the current page is a manuscript page
   * 
   * @return boolean
   */
  private function urlValid(){
    
    $page_title = $this->page_title;   
    $namespace = $this->namespace; 
    $document_root = $this->document_root;
    $images_root_dir = $this->images_root_dir; 
    
    if($namespace !== NS_MANUSCRIPTS){
      return false; 
    }
    
    $page_title_array = explode("/", $page_title);
    
    $user_fromurl = isset($page_title_array[0]) ? $page_title_array[0] : null; 
    $filename_fromurl = isset($page_title_array[1]) ? $page_title_array[1] : null;
    
    if(!isset($user_fromurl) || !isset($filename_fromurl) || !ctype_alnum($user_fromurl) || !ctype_alnum($filename_fromurl)){
      return false; 
    }
    
    $zoom_images_file = $document_root . DIRECTORY_SEPARATOR . $images_root_dir . DIRECTORY_SEPARATOR . $user_fromurl . DIRECTORY_SEPARATOR . $filename_fromurl;
    
    if(!file_exists($zoom_images_file)){
      return false; 
    }
    
    $this->user_fromurl = $user_fromurl;
    $this->filename_fromurl = $filename_fromurl; 
    
    return true;    
  }
  
  /**
   * Generates the HTML for the iframe
   * 
   * @return string
   */
  private function formatIframeHTML(){
    
    $mediawiki_dir  = $this->mediawiki_dir;
    $viewer_type = $this->getViewerType();
    
    if($viewer_type === 'js'){
      $viewer_path = 'tools/ajax-tiledviewer/ajax-tiledviewer.php';
      
    }else{
      $viewer_path = 'tools/zoomify/zoomifyviewer.php';  
    }
    
    $image_file_path = $this->constructImageFilePath();
    $lang = $this->lang;
    $siteName	= $this->title_options_site_name;
    
    $iframeHTML = '<iframe id="zoomviewerframe" src="' .  $mediawiki_dir . '/extensions/newManuscript/' . $viewer_path . '?image=' . $image_file_path . '&amp;lang=' . $lang . '&amp;sitename=' . urlencode($siteName) . '"></iframe>';
    
    return $iframeHTML;
  }
    
 /**
  * Get the default viewer type.
  * 
  * @return string
  */
   private function getViewerType(){
     
     if($this->viewer_type !== NULL){
       return $this->viewer_type;
     }
     
     if($this->browserIsIE()){
       return 'js'; 
     }
     
     return 'zv'; 
   }
   
 /**
  * Determines whether the browser is Internet Explorer.
  * 
  * @return bool
  */
   private function browserIsIE(){
     
     $user_agent = $_SERVER['HTTP_USER_AGENT'];
     
     if(preg_match('/MSIE/i', $user_agent)){
       return true; 
     }
     
     return false; 
   }
   
   /**
    * Constructs the full path of the image to be passed to the iframe.
    * 
    * @return string
    */
   private function constructImageFilePath(){
      
      $images_root_dir = $this->images_root_dir;
      $user_fromurl = $this->user_fromurl;
      $filename_fromurl = $this->filename_fromurl; 

      //DIRECTORY_SEPARATOR does not work here
      $image_file_path = '/' . $images_root_dir . '/' . $user_fromurl . '/' . $filename_fromurl . '/';
      
      return $image_file_path;
   }
  
  /**
   * The function register, registers the wikitext <pagemetatable> </pagemetatable>
   * with the parser, so that the metatable can be loaded. When these tags are encountered in the wikitext, the function renderPageMetaTable
   * is called. The metatable refers to meta data on a collection level, while the pagemetatable tags enable users to insert page-specific meta data
   */
  public static function register(Parser &$parser){
    
    // Register the hook with the parser
    $parser->setHook('pagemetatable', array('newManuscriptHooks', 'renderPageMetaTable'));
    return true;
  }
  
  /**
   * This function renders the pagemetatable, when the tags are encountered in the wikitext
   */
  public static function renderPageMetaTable($input, $args, Parser $parser){
      
    $page_meta_table = new pageMetaTable();
    $page_meta_table->extractOptions($input);
    
    return $page_meta_table->renderTable($input);    
  }
  
  /**
   * This function initialises the $summary_page_wrapper, retrieves the $meta_data for the current collection, and uses this $meta_data
   * to construct the meta table
   */
  private function renderMetaTable($collection_name){
        
    if(!isset($collection_name)){
      return; 
    }
    
    $summary_page_wrapper = new summaryPageWrapper('getmetadata',0,0,'','','', $collection_name);
    $meta_data = $summary_page_wrapper->retrieveFromDatabase($collection_name); 
      
    $collection_meta_table = new collectionMetaTable();   
    return $collection_meta_table->renderTable($meta_data);
  }
  
  /**
   * This function prevents users from moving a manuscript page
   * 
   * @param Title $oldTitle
   * @param Title $newTitle
   * @param User $user
   * @param type $error
   * @param type $reason
   * @return boolean
   */
  public function onAbortMove( Title $oldTitle, Title $newTitle, User $user, &$error, $reason ) {
    
    if($oldTitle->getNamespace() !== NS_MANUSCRIPTS){
      return true;      
    }
     
    $error = $this->getMessage('newmanuscripthooks-move');
  
    return false; 
  }
  
  /**
   * This function runs every time mediawiki gets a delete request. This function prevents
   * users from deleting manuscripts they have not uploaded
   * 
   * @param WikiPage $article
   * @param User $user
   * @param type $reason
   * @param type $error
   */
  public function onArticleDelete(WikiPage &$article, User &$user, &$reason, &$error){
    
    $this->assignGlobalsToProperties();
    
    $page_title = $this->page_title; 
    $namespace = $this->namespace; 
    
    if($namespace !== NS_MANUSCRIPTS){
      //this is not a manuscript page. Allow deletion
      return true; 
    }
    
    $user_name = $user->getName();  
    $user_groups = $user->getGroups();
    $page_title_array = explode("/", $page_title);
    $user_fromurl = isset($page_title_array[0]) ? $page_title_array[0] : null; 
 
    if(($user_fromurl === null || $user_name !== $user_fromurl) && !in_array('sysop',$user_groups)){     
        //deny deletion because the current user did not create this manuscript, and the user is not an administrator
        $error = "<br>" . $this->getMessage('newmanuscripthooks-nodeletepermission') . ".";
        return false; 
    }
    
    $document_root = $this->document_root; 
    $images_root_dir = $this->images_root_dir;
    
    $filename_fromurl = isset($page_title_array[1]) ? $page_title_array[1] : null; 
        
    $zoom_images_file = $document_root . DIRECTORY_SEPARATOR . $images_root_dir . DIRECTORY_SEPARATOR . $user_fromurl . DIRECTORY_SEPARATOR . $filename_fromurl;
    
    $url_count_size = $this->manuscript_url_count_size;
    
    //do not delete any additional files on server if the zoom images file does not exist,
    //if the url does not have the format of a manuscripts page, or if $filename_fromurl is null
    if(!file_exists($zoom_images_file) || count($page_title_array)!== $url_count_size || !isset($filename_fromurl)){
      
      return true; 
    }
    
    $this->user_fromurl = $user_fromurl; 
    $this->filename_fromurl = $filename_fromurl;
            
    $this->deleteExportFiles($zoom_images_file);
    
    $this->deleteOriginalImage();
        
    $collection_name = $this->new_manuscript_wrapper->getCollection($this->page_title_with_namespace); 
    $this->new_manuscript_wrapper->deleteDatabaseEntry($collection_name, $this->page_title_with_namespace);  
    $this->new_manuscript_wrapper->subtractAlphabetnumbers($filename_fromurl, $collection_name);    
    
    return true;    
  }
  
  /**
   * Check if all the default files are present, and delete all files
   */
  private function deleteExportFiles($zoom_images_file){
               
    $tile_group_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'TileGroup0';
    $image_properties_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'ImageProperties.xml';    
    
    if(!is_dir($tile_group_url) ||!is_file($image_properties_url)){
      return false; 
    }
    
    $this->zoomimage_check_before_delete = true; 
    
    return $this->deleteAllFiles($zoom_images_file);
  }
  
  /**
   * This function checks if the original image path file is valid, and then calls deleteAllFiles()
   * 
   * @return boolean
   */
  private function deleteOriginalImage(){
    
    $partial_original_image_path = $this->constructOriginalImagePath();
    $original_image_path = $this->document_root . $partial_original_image_path; 
    
    if(!is_dir($original_image_path)){
      return false; 
    }
    
    $file_scan = scandir($original_image_path);    
    $image_file = isset($file_scan[2])? $file_scan[2] : "";
    
    if($image_file === ""){
      return false;
    }
     
    $full_original_image_path = $original_image_path . $image_file; 
    
    if(!$this->isImage($full_original_image_path)){
      return false;
    }
    
    if (count($file_scan) > 3){
      return false; 
    }
    
    $this->original_image_check_before_delete = true; 
    
    return $this->deleteAllFiles($original_image_path);   
  }
     
  /**
   * This function deletes all files in $zoom_images_file. First a last check is done.
   * After this the function deletes files in $path
   *  
   * @param type $path
   * @return boolean
   */
  private function deleteAllFiles($path){
    
    if($this->zoomimage_check_before_delete || $this->original_image_check_before_delete){
    
      //start deleting files         
      if (is_dir($path) === true){
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file){
          //recursive call
          $this->deleteAllFiles(realpath($path) . DIRECTORY_SEPARATOR . $file);
        }

        return rmdir($path);

      }else if (is_file($path) === true){
        return unlink($path);
      }  
    }
    
    return false;   
  }  
    
  /**
   * This function prevents users from saving new wiki pages on NS_MANUSCRIPTS when there is no corresponding file in the database,
   * and it checks if the content is not larger than $max_charachters_manuscript  
   * 
   * @param type $wikiPage
   * @param type $user
   * @param type $content
   * @param type $summary
   * @param type $isMinor
   * @param type $isWatch
   * @param type $section
   * @param type $flags
   * @param type $status
   */
  public function onPageContentSave( &$wikiPage, &$user, &$content, &$summary,
    $isMinor, $isWatch, $section, &$flags, &$status){
    
    $this->assignGlobalsToProperties();
    
    $page_title_with_namespace = $this->page_title;
    $page_title = $this->page_title; 
    $namespace = $this->namespace; 
                 
    if($namespace !== NS_MANUSCRIPTS){
      //this is not a manuscript. Allow saving
      return true; 
    }
             
    $document_root = $this->document_root; 
    $images_root_dir = $this->images_root_dir;
      
    $page_title_array = explode("/", $page_title);
    
    $user_fromurl = isset($page_title_array[0]) ? $page_title_array[0] : null; 
    $filename_fromurl = isset($page_title_array[1]) ? $page_title_array[1] : null; 
    
    $zoom_images_file = $document_root . DIRECTORY_SEPARATOR . $images_root_dir . DIRECTORY_SEPARATOR . $user_fromurl . DIRECTORY_SEPARATOR . $filename_fromurl;
      
    if(!file_exists($zoom_images_file) || !isset($user_fromurl) || !isset($filename_fromurl)){
      
      //the page is in NS_MANUSCRIPTS but there is no corresponding file in the database, so don't allow saving    
      $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-nopermission') . "."));   
      return true; 
    }
    
    $new_content = $content->mText;
    
    $charachters_current_save = strlen($new_content);
    
    //check if this page does not have more charachters than $max_charachters_manuscript
    if($charachters_current_save > $this->max_charachters_manuscript){
      
       $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-maxchar1') . " " . $charachters_current_save . " " . 
           $this->getMessage('newmanuscripthooks-maxchar2') . " " . $this->max_charachters_manuscript . " " . $this->getMessage('newmanuscripthooks-maxchar3') . "."));   
       return true; 
    }
    
    //this is a manuscript page, there is a corresponding file in the database, and $max_charachters_manuscript has not been reached, so allow saving
    return true;
  }
    
  /**
   * This function adds additional modules containing CSS before the page is displayed
   * 
   * @param OutputPage $out
   * @param Skin $ski
   */
  public function onBeforePageDisplay(OutputPage &$out, Skin &$ski ){

    $title_object = $out->getTitle();
    
    //mPrefixedText is the page title with the namespace
    $page_title_with_namespace = $title_object->mPrefixedText; 
    
    if($title_object->getNamespace() === NS_MANUSCRIPTS){
      //add css for the metatable and the zoomviewer
      $out->addModuleStyles('ext.metatable');
       
      //meta table has to rendered here, because in this way it will be appended after the text html, and not before
      if($this->view){
        $collection = $this->new_manuscript_wrapper->getCollection($page_title_with_namespace);
        $html = $this->renderMetaTable($collection);
        $out->addHTML($html);
      }
           
    }elseif($page_title_with_namespace === 'Special:NewManuscript'){
      $out->addModuleStyles('ext.newmanuscriptcss');
      $out->addModules('ext.newmanuscriptloader');
    }
          
    return true; 
  }
  
  /**
   * This function visualizes <add> and <del> tags that are nested in themselves correctly. It also removes tags that are not available in the editor for visualization.
   * These tags will still be available in the editor. 
   * 
   * @param type $identifier
   * @return \type
   */
  public function onParserAfterTidy( &$parser, &$text ){
                 
    //look for stray </add> tags, and replace them with a tei-add span element  
    $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/add&gt;/','</span></span><span class="tei-add">$1</span>', $text);
    
    //look for stray </del> tags, and replace them with a tei-del span element  
    $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/del&gt;/','</span></span><span class="tei-del">$1</span>', $text);
    
    $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/hi&gt;/','</span></span><span class="tei-hi superscript">$1</span>', $text);
  
    //look for any other escaped tags, and remove them
    $text = preg_replace('/&lt;(.*?)&gt;/s','', $text);
        
    return true; 
  }

  /**
   * This function retrieves the message from the i18n file for String $identifier
   * 
   * @param type $identifier
   * @return type
   */
  public function getMessage($identifier){
    return wfMessage($identifier)->text();
  }
}