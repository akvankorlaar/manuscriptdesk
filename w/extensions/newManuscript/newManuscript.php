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
 */

/**
 * Usage: Add the following line in LocalSettings.php:
 * require_once( "$IP/extensions/newManuscript/newManuscript.php" );
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
	'name'           => 'newManuscript',
	'author'         => 'Arent van Korlaar',
	'version'        => '0.0.1',
	'url'            => '',
	'description'    => 'This extension permits users to create new manuscript pages for the Manuscript Desk.',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['newManuscriptHooks']    = $dir . '/newManuscript.hooks.php';
$wgAutoloadClasses['pageMetaTable'] = $dir . 'specials/pageMetaTable.php';
$wgAutoloadClasses['newManuscriptForm'] = $dir . 'specials/newManuscriptForm.php';
$wgAutoloadClasses['prepareSlicer'] = $dir . 'specials/prepareSlicer.php';
$wgAutoloadClasses['newManuscriptWrapper'] = $dir . 'specials/newManuscriptWrapper.php';
$wgExtensionMessagesFiles['NewManuscript']  = __DIR__ . '/newManuscript.i18n.php';

//Register auto load for the special page classes and register special pages
$wgAutoloadClasses['SpecialNewManuscript'] = $dir . '/specials/SpecialNewManuscript.php';

$wgSpecialPages['NewManuscript'] = 'SpecialNewManuscript';

//Extra files containing CSS and javascript loaded later 

$wgResourceModules['ext.zoomviewer'] = array(
		'localBasePath' => dirname( __FILE__ ),  
		'styles'  => 'css/ext.zoomviewer.css',
);

$wgResourceModules['ext.metatable'] = array(
		'localBasePath' => dirname( __FILE__ ),  
		'styles'  => 'css/ext.metatable.css',
);

$wgResourceModules['ext.newmanuscriptcss'] = array(
		'localBasePath' => dirname( __FILE__ ),  
		'styles'  => 'css/ext.newmanuscriptcss.css',
);

$wgResourceModules['ext.newmanuscriptloader' ] = array(
		'scripts'  => 'js/ext.newmanuscriptloader.js',
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'newManuscript',
);

//Instantiate the newManuscriptHooks class and register the hooks
$newManuscriptHooks = new newManuscriptHooks();

$wgHooks['EditPage::showEditForm:fields'][] = array($newManuscriptHooks, 'onEditPageShowEditFormInitial' );
$wgHooks['MediaWikiPerformAction'][] = array($newManuscriptHooks, 'onMediaWikiPerformAction');
$wgHooks['ParserFirstCallInit'][] = array($newManuscriptHooks, 'register');
$wgHooks['AbortMove'][] = array($newManuscriptHooks, 'onAbortMove');
$wgHooks['ArticleDelete'][] = array($newManuscriptHooks, 'onArticleDelete');
$wgHooks['PageContentSave'][] = array($newManuscriptHooks,'onPageContentSave');
$wgHooks['BeforePageDisplay'][] = array($newManuscriptHooks, 'onBeforePageDisplay');
