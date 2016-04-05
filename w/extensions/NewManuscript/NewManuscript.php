<?php
/**
 * This file is part of the NewManuscript extension
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
 * require_once( "$IP/extensions/NewManuscript/NewManuscript.php" );
 */

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
// Check environment
if ( !defined( 'MEDIAWIKI' ) ) {
  echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
  die( -1 );
}

//Credits
$wgExtensionCredits['parserhook'][] = array(
	'path'           => __FILE__,
	'name'           => 'NewManuscript',
	'author'         => 'Arent van Korlaar',
	'version'        => '0.0.1',
	'url'            => 'https://manuscriptdesk.uantwerpen.be',
	'description'    => 'This extension permits users to create new manuscript pages for the Manuscript Desk.',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['NewManuscriptImageValidator']    = $dir . 'specials/NewManuscriptImageValidator.php';
$wgAutoloadClasses['NewManuscriptDeleter'] = $dir . 'specials/NewManuscriptDeleter.php';
$wgAutoloadClasses['NewManuscriptPaths']    = $dir . 'specials/NewManuscriptPaths.php';
$wgAutoloadClasses['NewManuscriptRequestProcessor']    = $dir . 'specials/NewManuscriptRequestProcessor.php';
$wgAutoloadClasses['NewManuscriptUploadForm']    = $dir . 'specials/NewManuscriptUploadForm.php';
$wgAutoloadClasses['NewManuscriptViewer']    = $dir . 'specials/NewManuscriptViewer.php';
$wgAutoloadClasses['NewManuscriptWrapper']    = $dir . 'specials/NewManuscriptWrapper.php';
$wgAutoloadClasses['PageMetTableFromTags']    = $dir . 'specials/PageMetaTableFromTags.php';
$wgAutoloadClasses['SlicerExecuter']    = $dir . 'specials/SlicerExecuter.php';
$wgAutoloadClasses['SpecialNewManuscript'] = $dir . 'specials/SpecialNewManuscript.php';

$wgExtensionMessagesFiles['NewManuscript']  = $dir . 'NewManuscript.i18n.php';
$wgAutoloadClasses['NewManuscriptHooks']    = $dir . 'NewManuscript.hooks.php';

$wgSpecialPages['NewManuscript'] = 'SpecialNewManuscript';

//Extra files containing CSS and javascript loaded later 
$wgResourceModules['ext.zoomviewercss'] = array(
		'localBasePath' => dirname( __FILE__ ),  
		'styles'  => 'css/ext.zoomviewercss.css',
);

$wgResourceModules['ext.manuscriptpagecss'] = array(
		'localBasePath' => dirname( __FILE__ ),  
		'styles'  => 'css/ext.manuscriptpagecss.css',
);

$wgResourceModules['ext.newmanuscriptbuttoncontroller'] = array(
  'scripts' => array(
    'js/ext.newmanuscriptbuttoncontroller.js',
  ),
  'localBasePath' => __DIR__,
  'remoteExtPath' => 'NewManuscript',
);

//Instantiate the NewManuscriptHooks class and register the hooks
$helperscripts_hooks = new NewManuscriptHooks(new NewManuscriptWrapper());

$wgHooks['EditPage::showEditForm:fields'][] = array($helperscripts_hooks, 'onEditPageShowEditFormInitial' );
$wgHooks['MediaWikiPerformAction'][] = array($helperscripts_hooks, 'onMediaWikiPerformAction');
$wgHooks['ParserFirstCallInit'][] = array($helperscripts_hooks, 'register');
$wgHooks['AbortMove'][] = array($helperscripts_hooks, 'onAbortMove');
$wgHooks['ArticleDelete'][] = array($helperscripts_hooks, 'onArticleDelete');
$wgHooks['PageContentSave'][] = array($helperscripts_hooks,'onPageContentSave');
$wgHooks['BeforePageDisplay'][] = array($helperscripts_hooks, 'onBeforePageDisplay');
$wgHooks['ParserAfterTidy'][] = array($helperscripts_hooks, 'onParserAfterTidy');
$wgHooks['UnitTestsList'][] = array($helperscripts_hooks, 'onUnitTestsList');
