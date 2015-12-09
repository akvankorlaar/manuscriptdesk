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
 * 
 * Some of the functions have been obtained from includes/specials/SpecialUpload.php, and altered for the purpose of this extension
 */

class newManuscriptForm extends HTMLForm {
  
/**
 * This class displays the form on the Special:NewManuscript page. Parts of this class have been copied from includes/specials/specialUpload.php,
 * and altered for the purpose of this extension
 */
  
  public $max_upload_size; 
  
  private $collections_message; 
  private $allowed_file_extensions; 
  private $selected_collection; 
 
  //class constructor
  public function __construct(IContextSource $context = null, $collections_message, $selected_collection) {
    
    global $wgNewManuscriptOptions;
        
    $this->max_upload_size = $wgNewManuscriptOptions['max_upload_size'];
    $this->allowed_file_extensions =  $wgNewManuscriptOptions['allowed_file_extensions'];
    $this->collections_message = $collections_message; 
    $this->selected_collection = isset($selected_collection) ? $selected_collection : ''; 
    
    $descriptor = $this->getSourceSection();

    //reference to the parent constructor
    parent::__construct($descriptor, $context, 'upload'); 
    # Set some form properties
    $this->setSubmitText($this->msg('newmanuscript-submit'));
    $this->setSubmitName('wpUpload');
    # Used message keys: 'accesskey-upload', 'tooltip-upload'
    $this->setSubmitTooltip('upload');
    $this->setId('mw-upload-form');
  }
  
  /**
   * This function adds the title information, the title field, the uploadfile button and the extension information
   * 
   * @return string
   */
  private function getSourceSection(){
    
    $descriptor = array();
         
    $descriptor['title_message'] = array(
      'type' => 'info',
      'section' => 'title',
      'default' => '<br>' . $this->msg('newmanuscript-title-instruction') . '<br>',
      'raw' => true,    
    );          
    
    $descriptor['title_field'] = array(
      'section' => 'title',
      'label' => 'New Title:', 
      'class' => 'HTMLTextField', 
      'id' => 'enter_title',
      'maxlength'=> 50,
    );
    
    //shows after submit has been clicked
    $javascript_loader_message  = "<h3 id='newmanuscript-loaderdiv' style='display: none;'>Loading";
    $javascript_loader_message .= "<span id='newmanuscript-loaderspan'></span>";
    $javascript_loader_message .= "</h3>";
    
    if($this->collections_message === ""){
      $collections_message = "";
    }else{
      $collections_message = $this->collections_message . '.<br>';
    }
    
    $descriptor['collection_message'] = array(
      'type' => 'info',
      'section' => 'title',
      'default' => '<br>' . $this->msg('newmanuscript-collections-instruction') . '<br>' . $this->msg('newmanuscript-collections-instruction2') . '<br>' . $collections_message,
      'raw' => true,    
    );    
    
    $descriptor['collection_field'] = array(
      'section' => 'title',
      'label' => 'Collection:',
      'default' => $this->selected_collection, 
      'class' => 'HTMLTextField', 
      'maxlength'=> 50,
    );
    
    $descriptor['UploadFile'] = array(
      'class' => 'UploadSourceFile', //UploadSourceFile
      'section' => 'source',
      'type' => 'file',
      'id' => 'wpUploadFile',
      'label-message' => 'sourcefilename',
      'help' => $this->msg( 'upload-maxfilesize',
        $this->getContext()->getLanguage()->formatSize($this->max_upload_size))->parse() .
      $this->msg('word-separator' )->escaped() .
      $this->msg('upload_source_file' )->escaped(),
    );
    
    $descriptor['Extensions'] = array(
      'type' => 'info',
      'section' => 'source',
      'default' => $this->getExtensionsMessage(),
      'raw' => true,
    );
              
    //add html form entries
    return $descriptor;
  }
  
  /**
   * Retrieves a list of allowed file types. 
   * 
   * @return string
   */
  private function getExtensionsMessage() {
		
    $wg_modified_file_extensions = $this->allowed_file_extensions; 

    $extensionsList =
      '<div id="mw-upload-permitted">' .
      $this->msg(
        'upload-permitted',
        $this->getContext()->getLanguage()->commaList( array_unique($wg_modified_file_extensions) )
      )->parseAsBlock() .
      "</div>\n";
    
    return $extensionsList;
  }
  
  /**
   * Add the upload JS and show the form.
   */
  public function show() {
    $this->addUploadJS();
    parent::show();
  }
  
  /**
   * Add upload JS to the OutputPage (the JS construct the preview image)
   * 
   * Location of the javascript file:
   * resources/src/mediawiki.special/mediawiki.special.upload.js
   * 
   * Additional information about the modules can be found in resources/Resources.php
   */
  private function addUploadJS() {
    
    $scriptVars = array(
      'wgMaxUploadSize' => $this->max_upload_size, 
    );
    
    $out = $this->getOutput();
    $out->addJsConfigVars($scriptVars);
    $out->addModules( array(
      'mediawiki.special.upload', // Newer extras for thumbnail preview.
    ));
  }
}

class UploadSourceFile extends HTMLTextField {  
}


