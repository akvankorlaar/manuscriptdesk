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
 * require_once( "$IP/extensions/TEIExport/TEIExport.php" );
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
  'name' => 'TEIExport',
  'author' => 'Arent van Korlaar',
  'version' => '1.0',
  'url' => 'https://manuscriptdesk.uantwerpen.be',
  'description' => 'This extension provides an API to export text from the manuscript desk in TEI format',
);

//Shortcut to this extension directory
$dir = __DIR__ . '/';

//Auto load classes 
//$wgAutoloadClasses['SpecialOriginalImages'] = $dir . 'specials/SpecialOriginalImages.php';
$wgAutoloadClasses['SpecialCollectionTEIExport'] = $dir . 'specials/SpecialCollectionTEIExport.php';
$wgAutoloadClasses['TEIExportBase'] = $dir . 'specials/TEIExportBase.php';

//$wgSpecialPages['OriginalImages'] = 'SpecialOriginalImages';
$wgSpecialPages['CollectionTEIExport'] = 'SpecialCollectionTEIExport';
$wgExtensionMessagesFiles['TEIExport'] = $dir . 'TEIExport.i18n.php';
