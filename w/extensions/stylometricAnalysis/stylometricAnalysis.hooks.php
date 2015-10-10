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

class stylometricAnalysisHooks {
  
/**
 * Hooks for the stylometricAnalysis extension 
 */
   
  //class constructor 
  public function __construct(){    
  }
  
  /**
	 * This function sends configuration variables to javascript. In javascript they are accessed through 'mw.config.get('..') 
	 */
	public function onResourceLoaderGetConfigVars(&$vars){
    
		global $wgStylometricAnalysisOptions;
        
    $vars['wgmin_stylometricanalysis_pages'] = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_pages'];
		
		return true;
	}

  /**
   * This function loads additional modules containing CSS before the page is displayed
   * 
   * @param OutputPage $out
   * @param Skin $ski
   */
  public function onBeforePageDisplay(OutputPage &$out, Skin &$ski ){

    $title_object = $out->getTitle();
    $page_title = $title_object->getPrefixedURL();
    
    if($page_title === 'Special:StylometricAnalysis'){    
      $out->addModuleStyles('ext.stylometricanalysis');
      $out->addModules('ext.stylometricanalysisloader');
    }

    return true; 
  }
}

