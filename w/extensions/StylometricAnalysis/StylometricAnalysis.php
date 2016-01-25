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
/**
 * Usage: Add the following line in LocalSettings.php:
 * require_once( "$IP/extensions/StylometricAnalysis/StylometricAnalysis.php" );
 */
// Check environment
if (!defined('MEDIAWIKI')) {
    echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
    die(-1);
}

/* Configuration */

//Credits
$wgExtensionCredits['parserhook'][] = array(
  'path' => __FILE__,
  'name' => 'stylometricAnalysis',
  'author' => 'Arent van Korlaar',
  'version' => '0.0.1',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension permits users to perform Stylometric Analysis on texts for the Manuscript Desk.',
  'descriptionmsg' => 'stylometricanalysis-desc'
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['StylometricAnalysisHooks'] = $dir . '/StylometricAnalysis.hooks.php';
$wgAutoloadClasses['StylometricAnalysisWrapper'] = $dir . '/specials/StylometricAnalysisWrapper.php';
$wgExtensionMessagesFiles['StylometricAnalysis'] = $dir . '/StylometricAnalysis.i18n.php';

////Register auto load for the special page classes and register special pages
$wgAutoloadClasses['SpecialStylometricAnalysis'] = $dir . '/specials/SpecialStylometricAnalysis.php';

$wgSpecialPages['StylometricAnalysis'] = 'SpecialStylometricAnalysis';

//Extra file loaded later 
$wgResourceModules['ext.stylometricanalysis'] = array(
  'localBasePath' => dirname(__FILE__) . '/css',
  'styles' => '/ext.stylometricanalysis.css',
);

$wgResourceModules['ext.stylometricanalysisloader'] = array(
  'localBasePath' => dirname(__FILE__) . '/js',
  'scripts' => '/ext.stylometricanalysisloader.js',
  'messages' => array(
    'stylometricanalysis-error-manycollections',
  ),
);

//Instantiate the stylometricAnalysisHooks class and register the hooks
$StylometricAnalysisHooks = new StylometricAnalysisHooks();

$wgHooks['BeforePageDisplay'][] = array($StylometricAnalysisHooks, 'onBeforePageDisplay');
$wgHooks['ResourceLoaderGetConfigVars'][] = array($StylometricAnalysisHooks, 'onResourceLoaderGetConfigVars');
