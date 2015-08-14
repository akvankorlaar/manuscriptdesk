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
 * require_once( "$IP/extensions/collate/collate.php" );
 */

// Check environment
if (!defined( 'MEDIAWIKI')){
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}

/* Configuration */

// Credits
$wgExtensionCredits['parserhook'][] = array(
	'path'           => __FILE__,
	'name'           => 'collate',
	'author'         => 'Arent van Korlaar',
	'version'        => '0.0.1',
	'url'            => '',
	'description'    => 'Allow collation of text',
	'descriptionmsg' =>  '',
);

// Shortcut to this extension directory
$dir = __DIR__ . '/';

// Auto load classes 
$wgAutoloadClasses['summaryPagesHooks']    = $dir . '/summaryPages.hooks.php';
$wgExtensionMessagesFiles['summaryPages']  = __DIR__ . '/summaryPages.i18n.php';

// Register auto load for the special page classes and register special pages
$wgAutoloadClasses['SpecialuserPage'] = $dir . '/specials/SpecialuserPage.php';
$wgAutoloadClasses['SpecialallManuscriptPages'] = $dir . '/specials/SpecialallManuscriptPages.php';
$wgAutoloadClasses['SpecialallCollections'] = $dir . '/specials/SpecialallCollections.php';
$wgAutoloadClasses['SpecialrecentManuscriptPages'] = $dir . '/specials/SpecialrecentManuscriptPages.php';
$wgAutoloadClasses['SpecialallCollations'] = $dir . '/specials/SpecialallCollations.php';
$wgAutoloadClasses['baseSummaryPage'] = $dir . '/specials/baseSummaryPage.php';

$wgSpecialPages['userPage'] = 'SpecialuserPage';
$wgSpecialPages['allManuscriptPages'] = 'SpecialallManuscriptPages';
$wgSpecialPages['allCollections'] = 'SpecialallCollections';
$wgSpecialPages['recentManuscriptPages'] = 'SpecialrecentManuscriptPages';
$wgSpecialPages['allCollations'] = 'SpecialallCollations';

//Extra file loaded later 

$wgResourceModules['ext.buttonStyles'] = array(
  	'localBasePath' => dirname( __FILE__ ) . '/css',  
		'styles'  => '/ext.buttonStyles.css',
);

////Instantiate the collateHooks class and register the hooks
$summary_pages_hooks_object = new summaryPagesHooks();

$wgHooks['BeforePageDisplay'][] = array($summary_pages_hooks_object, 'onBeforePageDisplay');



