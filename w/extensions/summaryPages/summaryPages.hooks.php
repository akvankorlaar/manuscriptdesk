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
 */

class summaryPagesHooks {
    
  /**
   * This function adds additional modules containing CSS before the page is displayed
   * 
   * @param OutputPage $out
   * @param Skin $ski
   */
  public function onBeforePageDisplay(OutputPage &$out, Skin &$ski ){

    $title_object = $out->getTitle();
    $page_title = $title_object->mPrefixedText; 

    if($page_title === 'Special:UserPage' || $page_title === 'Special:AllCollections'|| $page_title === 'Special:AllManuscriptPages' || $page_title === 'Special:AllCollations'){
      //add css for correct button display on 'All Manuscripts' and 'User Page'
      $out->addModuleStyles("ext.buttonStyles");    
    }
    
    return true; 
  }
}
