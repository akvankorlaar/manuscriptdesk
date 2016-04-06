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
 * require_once( "$IP/extensions/HelperScripts/HelperScripts.php" );
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
  'name' => 'HelperScripts',
  'author' => 'Arent van Korlaar',
  'version' => '0.0.1',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension provides helper scripts for the Manuscript Desk',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
$wgAutoloadClasses['SpecialHelperScripts'] = $dir . 'specials/SpecialHelperScripts.php';
$wgAutoloadClasses['HelperScriptsViewer'] = $dir . 'specials/HelperScriptsViewer.php';
$wgAutoloadClasses['UpdateAlphabetNumbersWrapper'] = $dir . 'specials/UpdateAlphabetNumbersWrapper.php';
$wgAutoloadClasses['HelperScriptsDeleteWrapper'] = $dir . 'specials/HelperScriptsDeleteWrapper.php';
$wgAutoloadClasses['HelperScriptsRequestProcessor'] = $dir . 'specials/HelperScriptsRequestProcessor.php';

$wgAutoloadClasses['ManuscriptDeskDeleteWrapper'] = $dir . 'ManuscriptDeskDeleteWrapper.php';
$wgAutoloadClasses['ManuscriptDeskDeleter'] = $dir . 'ManuscriptDeskDeleter.php';

$wgAutoloadClasses['HelperScriptsHooks'] = $dir . 'HelperScripts.hooks.php';
$wgExtensionMessagesFiles['HelperScripts'] = $dir . 'HelperScripts.i18n.php';

$wgSpecialPages['HelperScripts'] = 'SpecialHelperScripts';

//Instantiate the NewManuscriptHooks class and register the hooks
$helperscripts_hooks = new HelperScriptsHooks();

$wgHooks['BeforePageDisplay'][] = array($helperscripts_hooks, 'onBeforePageDisplay');
$wgHooks['UnitTestsList'][] = array($helperscripts_hooks, 'onUnitTestsList');
