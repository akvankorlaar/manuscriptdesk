<?php

/**
 * This file is part of the Manuscript Desk (github.com/akvankorlaar/manuscriptdesk)
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
 * require_once( "$IP/extensions/ManuscriptDeskImages/ManuscriptDeskImages.php" );
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
  'name' => 'ManuscriptDeskImages',
  'author' => 'Arent van Korlaar',
  'version' => '1.0',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension is an API that can access the images uploaded to the Manuscript Desk',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['SpecialOriginalImages'] = $dir . 'specials/SpecialOriginalImages.php';
$wgAutoloadClasses['SpecialZoomImages'] = $dir . 'specials/SpecialZoomImages.php';

$wgSpecialPages['OriginalImages'] = 'SpecialOriginalImages';
$wgSpecialPages['ZoomImages'] = 'SpecialZoomImages';

//$wgExtensionMessagesFiles['ManuscriptDeskImages'] = $dir . 'ManuscriptDeskImages.i18n.php';
//$wgAutoloadClasses['ManuscriptDeskImagesHooks'] = $dir . 'ManuscriptDeskImages.hooks.php';

//Extra files containing CSS and javascript loaded later 
//$wgResourceModules['ext.zoomviewercss'] = array(
//  'localBasePath' => dirname(__FILE__),
//  'styles' => 'css/ext.zoomviewercss.css',
//);
//
//$wgResourceModules['ext.manuscriptpagecss'] = array(
//  'localBasePath' => dirname(__FILE__),
//  'styles' => 'css/ext.manuscriptpagecss.css',
//);
//
//$wgResourceModules['ext.newmanuscriptbuttoncontroller'] = array(
//  'scripts' => array(
//    'js/ext.newmanuscriptbuttoncontroller.js',
//  ),
//  'localBasePath' => __DIR__,
//  'remoteExtPath' => 'ManuscriptDeskImages',
//);
//
////Instantiate the ManuscriptDeskImagesHooks class and register the hooks
//$manuscriptdeskimages_hooks = ObjectRegistry::getInstance()->getManuscriptDeskImagesHooks();
//
//$wgHooks['EditPage::showEditForm:fields'][] = array($manuscriptdeskimages_hooks, 'onEditPageShowEditFormInitial');
//$wgHooks['MediaWikiPerformAction'][] = array($manuscriptdeskimages_hooks, 'onMediaWikiPerformAction');
//$wgHooks['MediaWikiPerformAction'][] = array($manuscriptdeskimages_hooks, 'onMediaWikiPerformRenderAction');
//$wgHooks['RawPageViewBeforeOutput'][] = array($manuscriptdeskimages_hooks, 'onRawPageViewBeforeOutput');
//$wgHooks['ParserFirstCallInit'][] = array($manuscriptdeskimages_hooks, 'register');
//$wgHooks['AbortMove'][] = array($manuscriptdeskimages_hooks, 'onAbortMove');
//$wgHooks['ArticleDelete'][] = array($manuscriptdeskimages_hooks, 'onArticleDelete');
//$wgHooks['PageContentSave'][] = array($manuscriptdeskimages_hooks, 'onPageContentSave');
//$wgHooks['BeforePageDisplay'][] = array($manuscriptdeskimages_hooks, 'onBeforePageDisplay');
//$wgHooks['UnitTestsList'][] = array($manuscriptdeskimages_hooks, 'onUnitTestsList');
//$wgHooks['OutputPageParserOutput'][] = array($manuscriptdeskimages_hooks, 'onOutputPageParserOutput');
