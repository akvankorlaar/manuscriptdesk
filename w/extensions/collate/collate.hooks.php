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

class collateHooks {
  
/**
 * Hooks for the collate extension 
 */
   
  //class constructor 
  public function __construct(){    
  }

  /**
   * This function retrieves the collatex output from the database, renders the table, and adds it to the output
   * 
   * @param type $output
   * @param type $article
   * @param type $title
   * @param type $user
   * @param type $request
   * @param type $wiki
   */
  public function onMediaWikiPerformAction($output, $article, $title, $user, $request, $wiki){

    if($wiki->getAction($request) !== 'view' ){
      return true; 
    }   

    $namespace = $title->getNamespace();     

    if($namespace !== NS_COLLATIONS){
      //this is not a collation page.
      return true; 
    }

    $page_title_with_namespace = $title->getPrefixedUrl();    

    $status = $this->getCollations($page_title_with_namespace);

    //something went wrong when retrieving the values from the database 
    if(!$status){
      return true; 
    }

    list($user_name, $date, $titles_array, $collatex_output) = $status; 

    $titles_array = json_decode($titles_array);

    $collate = new collate();

    $html_output = $collate->renderTable($titles_array, $collatex_output, $user_name, $date);

    //something went wrong when rendering the table
    if(!$html_output){
      return true; 
    }

    $output->addHTML($html_output);
  
    return true; 
  }

/**
 * This function retrieves data from the 'collations' table
 * 
 * @param type $url
 * @return boolean
 */
  private function getCollations($url){

    $dbr = wfGetDB(DB_SLAVE);

    //Database query
    $res = $dbr->select(
      'collations', //from
      array(
        'collations_user',//values
        'collations_url',
        'collations_date',
        'collations_titles_array',
        'collations_collatex'
         ),
      array(
      'collations_url = ' . $dbr->addQuotes($url), //conditions  
      ),
      __METHOD__ 
      );

      //there should be exactly 1 result
    if ($res->numRows() === 1){
      $s = $res->fetchObject();

      $user_name = $s->collations_user;
      $date = $s->collations_date; 
      $titles_array = $s->collations_titles_array;
      $collatex_output = $s->collations_collatex;

      return array($user_name, $date, $titles_array,$collatex_output);

    }else{

      return false; 
    }     
  }

  /**
   * This function prevents users from making any pages on NS_COLLATIONS, if they are not creating this page
   * through the collation extension. 
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
    
    global $wgRequest;
    
    $title_object = $wikiPage->getTitle();
    $namespace = $title_object->getNamespace();     
                       
    if($namespace !== NS_COLLATIONS){
      //this is not a collation page. Allow saving
      return true; 
    }
    
    $page_title_with_namespace = $title_object->getPrefixedUrl();    
    
    $title_object = Title::newFromText($page_title_with_namespace);
            
    //do not allow to make a new page if title object does not exist and 'save_current_table' was not posted. Leave it like this for now, but in future
    //maybe check if you can also use mediawiki's edit token for example.. 
    if(!$title_object->exists() && !$wgRequest->getText('save_current_table')){
      $status->fatal(new RawMessage($this->getMessage('collatehooks-nopermission'))); 
      return true;
    }
    
    //allow to make changes or a new page
    return true;   
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
    
    if($oldTitle->getNamespace() !== NS_COLLATIONS){
      return true; 
    }
    
    $error = $this->getMessage('collatehooks-move');
    
    return false; 
  }
  
   /**
   * This function runs every time mediawiki gets a delete request. This function prevents
   * users from deleting collations they have not uploaded
   * 
   * @param WikiPage $article
   * @param User $user
   * @param type $reason
   * @param type $error
   */
  public function onArticleDelete( WikiPage &$article, User &$user, &$reason, &$error ){
            
    $title_object = $article->getTitle();
    $namespace = $title_object->getNamespace();
                       
    if($namespace !== NS_COLLATIONS){
      //this is not a collation page. Allow saving
      return true; 
    }
    
    $page_title = $title_object->getPartialURL();  
               
    $page_title_array = explode("/", $page_title);
    $user_fromurl = isset($page_title_array[0]) ? $page_title_array[0] : null; 
    $user_name = $user->getName();  
    $user_groups = $user->getGroups();
    
    if(($user_fromurl === null || $user_name !== $user_fromurl) && !in_array('sysop',$user_groups)){     
        //deny deletion because the current user did not create this collation, and the user is not an administrator
        $error = '<br>' . $this->getMessage('collatehooks-nodeletepermission') . '.';
        return false; 
    }
    
    $this->deleteDatabaseEntry($title_object->getPrefixedURL()); 
    
    return true; 
  }
  
  /**
   * This function deletes the entry for corresponding to the page in the 'collations' table
   */
  private function deleteDatabaseEntry($page_title_with_namespace){
        
    $dbw = wfGetDB(DB_MASTER);
    
    $dbw->delete( 
      'collations', //from
      array( 
      'collations_url' => $page_title_with_namespace //conditions
        ), 
      __METHOD__ 
        );
    
    if ($dbw->affectedRows()){
      //something was deleted from the manuscripts table  
      return true;
    }else{
      //nothing was deleted
      return false;
    }
  }
    
  /**
   * This function loads additional modules containing CSS before the page is displayed
   * 
   * @param OutputPage $out
   * @param Skin $ski
   */
  public function onBeforePageDisplay(OutputPage &$out, Skin &$ski ){

    $page_title_with_namespace = $out->getTitle()->getPrefixedURL();
    $namespace = $out->getTitle()->getNamespace();
    
    if($namespace === NS_COLLATIONS || $page_title_with_namespace === 'Special:BeginCollate'){
      //add css for the collation table    
      $out->addModuleStyles('ext.collate');
      $out->addModules('ext.collateloader');
    }

    return true; 
  }
  
  /**
	 * This function sends configuration variables to javascript. In javascript they are accessed through 'mw.config.get('..') 
	 */
	public function onResourceLoaderGetConfigVars(&$vars){
    
    global $wgCollationOptions;
    
    $vars['wgmax_collation_pages'] = $wgCollationOptions['wgmax_collation_pages'];
    $vars['wgmin_collation_pages'] = $wgCollationOptions['wgmin_collation_pages'];
    
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
