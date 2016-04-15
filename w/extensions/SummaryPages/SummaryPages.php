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
 * require_once( "$IP/extensions/SummaryPages/SummaryPages.php" );
 */
//Check environment
if (!defined('MEDIAWIKI')) {
    echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
    die(-1);
}

/* Configuration */

//Credits
$wgExtensionCredits['parserhook'][] = array(
  'path' => __FILE__,
  'name' => 'SummaryPages',
  'author' => 'Arent van Korlaar',
  'version' => '0.0.1',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'Various special pages used to summarize data for the Manuscript Desk.',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['SummaryPagesHooks'] = $dir . '/SummaryPages.hooks.php';
$wgExtensionMessagesFiles['SummaryPages'] = __DIR__ . '/SummaryPages.i18n.php';

$wgAutoloadClasses['SpecialAllCollations'] = $dir . '/specials/AllCollations/SpecialAllCollations.php';
$wgAutoloadClasses['AllCollationsViewer'] = $dir . '/specials/AllCollations/AllCollationsViewer.php';
$wgAutoloadClasses['AllCollationsWrapper'] = $dir . '/specials/AllCollations/AllCollationsWrapper.php';

$wgAutoloadClasses['SpecialAllCollections'] = $dir . '/specials/AllCollections/SpecialAllCollections.php';
$wgAutoloadClasses['AllCollectionsViewer'] = $dir . '/specials/AllCollections/AllCollectionsViewer.php';
$wgAutoloadClasses['AllCollectionsWrapper'] = $dir . '/specials/AllCollections/AllCollectionsWrapper.php';

$wgAutoloadClasses['SpecialSingleManuscriptPages'] = $dir . '/specials/SingleManuscriptPages/SpecialSingleManuscriptPages.php';
$wgAutoloadClasses['SingleManuscriptPagesWrapper'] = $dir . '/specials/SingleManuscriptPages/SingleManuscriptPagesWrapper.php';
$wgAutoloadClasses['SingleManuscriptPagesViewer'] = $dir . '/specials/SingleManuscriptPages/SingleManuscriptPagesViewer.php';

$wgAutoloadClasses['SpecialRecentManuscriptPages'] = $dir . '/specials/RecentManuscriptPages/SpecialRecentManuscriptPages.php';

$wgAutoloadClasses['SpecialUserPage'] = $dir . '/specials/UserPage/SpecialUserPage.php';
$wgAutoloadClasses['UserPageCollationsViewer'] = $dir . '/specials/UserPage/UserPageCollationsViewer.php';
$wgAutoloadClasses['UserPageCollectionsViewer'] = $dir . '/specials/UserPage/UserPageCollectionsViewer.php';
$wgAutoloadClasses['UserPageDefaultViewer'] = $dir . '/specials/UserPage/UserPageDefaultViewer.php';
$wgAutoloadClasses['UserPageManuscriptsViewer'] = $dir . '/specials/UserPage/UserPageManuscriptsViewer.php';
$wgAutoloadClasses['UserPageRequestProcessor'] = $dir . '/specials/UserPage/UserPageRequestProcessor.php';
$wgAutoloadClasses['UserPageViewerInterface'] = $dir . '/specials/UserPage/UserPageViewerInterface.php';

$wgAutoloadClasses['HTMLJavascriptLoaderDots'] = $dir . '/specials/HTMLJavascriptLoaderDots.php';
$wgAutoloadClasses['HTMLLetterBar'] = $dir . '/specials/HTMLLetterBar.php';
$wgAutoloadClasses['HTMLPreviousNextPageLinks'] = $dir . '/specials/HTMLPreviousNextPageLinks.php';
$wgAutoloadClasses['HTMLUserPageMenuBar'] = $dir . '/specials/HTMLUserPageMenuBar.php';

$wgAutoloadClasses['SummaryPageBase'] = $dir . '/specials/SummaryPageBase.php';
$wgAutoloadClasses['SummaryPageRequestProcessor'] = $dir . '/specials/SummaryPageRequestProcessor.php';
$wgAutoloadClasses['SummaryPageViewerInterface'] = $dir . '/specials/SummaryPageViewerInterface.php';

$wgSpecialPages['AllCollations'] = 'SpecialAllCollations';
$wgSpecialPages['AllCollections'] = 'SpecialAllCollections';
$wgSpecialPages['RecentManuscriptPages'] = 'SpecialRecentManuscriptPages';
$wgSpecialPages['SingleManuscriptPages'] = 'SpecialSingleManuscriptPages';
$wgSpecialPages['UserPage'] = 'SpecialUserPage';

//Extra file loaded later 
$wgResourceModules['ext.userpagecss'] = array(
  'localBasePath' => dirname(__FILE__) . '/css',
  'styles' => '/ext.userpagecss.css',
);

$wgResourceModules['ext.javascriptloaderdots'] = array(
  'scripts' => array(
    'js/ext.javascriptloaderdots.js',
  ),
  'localBasePath' => __DIR__,
);

//Instantiate the collateHooks class and register the hooks
$summary_pages_hooks = new SummaryPagesHooks();

$wgHooks['BeforePageDisplay'][] = array($summary_pages_hooks, 'onBeforePageDisplay');
$wgHooks['UnitTestsList'][] = array($summary_pages_hooks, 'onUnitTestsList');
