<?php

/**
 * This file is part of the Collate extension
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
 * require_once( "$IP/extensions/Collate/Collate.php" );
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
  'name' => 'Collate',
  'author' => 'Arent van Korlaar',
  'version' => '0.0.1',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension permits users to collate texts for the Manuscript Desk.',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

$dir2 = dirname(__FILE__);
$dirbasename = basename($dir2);

//Auto load classes 
$wgAutoloadClasses['CollateHooks'] = $dir . '/Collate.hooks.php';
$wgAutoloadClasses['TextConverter'] = $dir . '/specials/CollatexConverter.php'; 
$wgAutoloadClasses['CollateWrapper'] = $dir . '/specials/CollateWrapper.php';
$wgAutoloadClasses['CollateViewer'] = $dir . '/specials/CollateViewer.php';
$wgExtensionMessagesFiles['Collate'] = __DIR__ . '/Collate.i18n.php';

//Register auto load for the special page classes and register special pages
$wgAutoloadClasses['SpecialCollate'] = $dir . '/specials/SpecialCollate.php';

$wgSpecialPages['Collate'] = 'SpecialCollate';

//Extra file loaded later 
$wgResourceModules['ext.collate'] = array(
  'localBasePath' => dirname(__FILE__),
  'styles' => '/css/ext.collate.css',
);

$wgResourceModules['ext.collateloader'] = array(
  'scripts' => array(
    'js/ext.collateloader.js',
  ),
  'localBasePath' => __DIR__,
  'remoteExtPath' => 'collate',
  'messages' => array(
    'collate-error-manytexts',
  ),
);

//Instantiate the CollateHooks class and register the hooks
$CollateHooks = new CollateHooks();

$wgHooks['MediaWikiPerformAction'][] = array($CollateHooks, 'onMediaWikiPerformAction');
$wgHooks['AbortMove'][] = array($CollateHooks, 'onAbortMove');
$wgHooks['ArticleDelete'][] = array($CollateHooks, 'onArticleDelete');
$wgHooks['PageContentSave'][] = array($CollateHooks, 'onPageContentSave');
$wgHooks['BeforePageDisplay'][] = array($CollateHooks, 'onBeforePageDisplay');
$wgHooks['ResourceLoaderGetConfigVars'][] = array($CollateHooks, 'onResourceLoaderGetConfigVars');
