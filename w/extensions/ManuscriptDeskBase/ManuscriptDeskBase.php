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
 * require_once( "$IP/extensions/ManuscriptDeskBase/ManuscriptDeskBase.php" );
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
  'name' => 'ManuscriptDeskBase',
  'author' => 'Arent van Korlaar',
  'version' => '0.0.1',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension provides base classes and messages for the Manuscript Desk',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';


//Auto load classes 
$wgAutoloadClasses['ManuscriptDeskBaseHooks'] = $dir . 'ManuscriptDeskBaseHooks.php';
$wgAutoloadClasses['ManuscriptDeskBaseSpecials'] = $dir . 'ManuscriptDeskBaseSpecials.php';
$wgAutoloadClasses['ManuscriptDeskBaseValidator'] = $dir . 'ManuscriptDeskBaseValidator.php';
$wgAutoloadClasses['ManuscriptDeskBaseWrapper'] = $dir . 'ManuscriptDeskBaseWrapper.php';
$wgAutoloadClasses['ManuscriptDeskBaseRequestProcessor'] = $dir . 'ManuscriptDeskBaseRequestProcessor.php';
$wgAutoloadClasses['ManuscriptDeskBaseTextProcessor'] = $dir . 'ManuscriptDeskBaseTextProcessor.php';
$wgExtensionMessagesFiles['ManuscriptDeskBaseMessages'] = $dir . 'ManuscriptDeskBaseMessages.i18n.php';

$wgAutoloadClasses['ManuscriptDeskBaseViewer'] = $dir . 'BaseViews' . '/' . 'ManuscriptDeskBaseViewer.php';
$wgAutoloadClasses['HTMLUploadError'] = $dir . 'BaseViews' . '/' . 'HTMLUploadError.php';
$wgAutoloadClasses['HTMLJavascriptLoader'] = $dir . 'BaseViews' . '/' . 'HTMLJavascriptLoader.php';

$wgResourceModules['ext.javascriptloader'] = array(
  'scripts' => array(
    'js/ext.javascriptloader.js',
  ),
  'localBasePath' => __DIR__,
  'remoteExtPath' => 'ManuscriptDeskBase',
);

$wgResourceModules['ext.manuscriptdeskbasecss'] = array(
  'localBasePath' => dirname(__FILE__),
  'styles' => '/css/ext.manuscriptdeskbasecss.css',
);


////Extra file loaded later 
//$wgResourceModules['ext.collatecss'] = array(
//  'localBasePath' => dirname(__FILE__),
//  'styles' => '/css/ext.collatecss.css',
//);
