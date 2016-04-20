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
if (!defined('MEDIAWIKI')) {
    echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
    die(-1);
}

//Credits
$wgExtensionCredits['parserhook'][] = array(
  'path' => __FILE__,
  'name' => 'NewManuscript',
  'author' => 'Arent van Korlaar',
  'version' => '0.0.1',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension permits users to create new manuscript pages for the Manuscript Desk.',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['NewManuscriptImageValidator'] = $dir . 'specials/NewManuscriptImageValidator.php';
$wgAutoloadClasses['NewManuscriptPaths'] = $dir . 'specials/NewManuscriptPaths.php';
$wgAutoloadClasses['NewManuscriptRequestProcessor'] = $dir . 'specials/NewManuscriptRequestProcessor.php';
$wgAutoloadClasses['NewManuscriptUploadForm'] = $dir . 'specials/NewManuscriptUploadForm.php';
$wgAutoloadClasses['NewManuscriptViewer'] = $dir . 'specials/NewManuscriptViewer.php';
$wgAutoloadClasses['NewManuscriptWrapper'] = $dir . 'specials/NewManuscriptWrapper.php';
$wgAutoloadClasses['PageMetTableFromTags'] = $dir . 'specials/PageMetaTableFromTags.php';
$wgAutoloadClasses['SlicerExecuter'] = $dir . 'specials/SlicerExecuter.php';
$wgAutoloadClasses['SpecialNewManuscript'] = $dir . 'specials/SpecialNewManuscript.php';

$wgExtensionMessagesFiles['NewManuscript'] = $dir . 'NewManuscript.i18n.php';
$wgAutoloadClasses['NewManuscriptHooks'] = $dir . 'NewManuscript.hooks.php';

$wgAutoloadClasses['DatabaseTestInserter'] = $dir . '/tests/DatabaseTestInserter.php';

$wgSpecialPages['NewManuscript'] = 'SpecialNewManuscript';

//Extra files containing CSS and javascript loaded later 
$wgResourceModules['ext.zoomviewercss'] = array(
  'localBasePath' => dirname(__FILE__),
  'styles' => 'css/ext.zoomviewercss.css',
);

$wgResourceModules['ext.manuscriptpagecss'] = array(
  'localBasePath' => dirname(__FILE__),
  'styles' => 'css/ext.manuscriptpagecss.css',
);

$wgResourceModules['ext.newmanuscriptbuttoncontroller'] = array(
  'scripts' => array(
    'js/ext.newmanuscriptbuttoncontroller.js',
  ),
  'localBasePath' => __DIR__,
  'remoteExtPath' => 'NewManuscript',
);

//initialise wrappers for database calls
$newmanuscript_wrapper = new NewManuscriptWrapper(null, new AlphabetNumbersWrapper(), new SignatureWrapper());

//Instantiate the NewManuscriptHooks class and register the hooks
$newmanuscript_hooks = new NewManuscriptHooks($newmanuscript_wrapper);

$wgHooks['EditPage::showEditForm:fields'][] = array($newmanuscript_hooks, 'onEditPageShowEditFormInitial');
$wgHooks['MediaWikiPerformAction'][] = array($newmanuscript_hooks, 'onMediaWikiPerformAction');
$wgHooks['ParserFirstCallInit'][] = array($newmanuscript_hooks, 'register');
$wgHooks['AbortMove'][] = array($newmanuscript_hooks, 'onAbortMove');
$wgHooks['ArticleDelete'][] = array($newmanuscript_hooks, 'onArticleDelete');
$wgHooks['PageContentSave'][] = array($newmanuscript_hooks, 'onPageContentSave');
$wgHooks['BeforePageDisplay'][] = array($newmanuscript_hooks, 'onBeforePageDisplay');
$wgHooks['UnitTestsList'][] = array($newmanuscript_hooks, 'onUnitTestsList');
$wgHooks['OutputPageParserOutput'][] = array($newmanuscript_hooks, 'onOutputPageParserOutput');
