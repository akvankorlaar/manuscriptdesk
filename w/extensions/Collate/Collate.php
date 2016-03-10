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
$wgAutoloadClasses['CollatexConverter'] = $dir . '/specials/CollatexConverter.php'; 
$wgAutoloadClasses['CollateWrapper'] = $dir . '/specials/CollateWrapper.php';
$wgAutoloadClasses['CollateViewer'] = $dir . '/specials/CollateViewer.php';
$wgAutoloadClasses['CollateRequestProcessor'] = $dir . 'specials/CollateRequestProcessor.php';
$wgExtensionMessagesFiles['Collate'] = __DIR__ . '/Collate.i18n.php';

//Register auto load for the special page classes and register special pages
$wgAutoloadClasses['SpecialCollate'] = $dir . '/specials/SpecialCollate.php';

$wgSpecialPages['Collate'] = 'SpecialCollate';

//Extra file loaded later 
$wgResourceModules['ext.collatecss'] = array(
  'localBasePath' => dirname(__FILE__),
  'styles' => '/css/ext.collatecss.css',
);

$wgResourceModules['ext.collatebuttoncontroller'] = array(
  'scripts' => array(
    'js/ext.collatebuttoncontroller.js',
  ),
  'localBasePath' => __DIR__,
  'remoteExtPath' => 'Collate',
  'messages' => array(
    'collate-error-manytexts',
  ),
);

//Instantiate the CollateHooks class and register the hooks
$collate_hooks = new CollateHooks();

$wgHooks['MediaWikiPerformAction'][] = array($collate_hooks, 'onMediaWikiPerformAction');
$wgHooks['AbortMove'][] = array($collate_hooks, 'onAbortMove');
$wgHooks['ArticleDelete'][] = array($collate_hooks, 'onArticleDelete');
$wgHooks['PageContentSave'][] = array($collate_hooks, 'onPageContentSave');
$wgHooks['BeforePageDisplay'][] = array($collate_hooks, 'onBeforePageDisplay');
$wgHooks['ResourceLoaderGetConfigVars'][] = array($collate_hooks, 'onResourceLoaderGetConfigVars');
