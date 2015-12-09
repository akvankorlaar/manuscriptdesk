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

class JBTEIToolbarHooks {

	/**
	 * editPageShowEditFormInitial hook
	 *
	 * Adds the modules to the edit form
	 *
	 * @param $toolbar array list of toolbar items
	 * @return bool
	 */
	public function editPageShowEditFormInitial($toolbar , $out){

	  $title_object = $out->getTitle();
    
	  if(!$this->isInEditMode($title_object)){
	    return true;
	  }
        
      $out->addModuleStyles('ext.JBTEIToolbarcss');
	  $out->addModules('ext.JBTEIToolbar');

	  return true;
	}

    /**
     * This function checks if the current $pageTitle is in edit mode
     * 
     * @global type $wgNewManuscriptOptions
     * @global type $wgWebsiteRoot
     * @param type $page_title
     * @return boolean
     */
	private function isInEditMode($title_object){
    
      global $wgNewManuscriptOptions,$wgWebsiteRoot; 

      $images_root_dir = $wgNewManuscriptOptions['zoomimages_root_dir'];

      //mTextform is the page title without namespace
      $page_title = $title_object->mTextform; 

      $page_title_array = explode("/", $page_title);

      $user_fromurl = isset($page_title_array[0]) ? $page_title_array[0] : null;
      $filename_fromurl = isset($page_title_array[1]) ? $page_title_array[1] : null;

      if(!isset($user_fromurl) || !isset($filename_fromurl) || !ctype_alnum($user_fromurl) || !ctype_alnum($filename_fromurl)){
        return false; 
      }

      $document_root = $wgWebsiteRoot;  
      $zoom_images_file = $document_root . DIRECTORY_SEPARATOR . $images_root_dir . DIRECTORY_SEPARATOR . $user_fromurl . DIRECTORY_SEPARATOR . $filename_fromurl;

      if(!file_exists($zoom_images_file)){
        return false; 
      }

      return true;
    }
}