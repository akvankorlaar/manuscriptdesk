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

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
// Check environment
if ( !defined( 'MEDIAWIKI' ) ) {
	echo('This is an extension to the MediaWiki package and cannot be run standalone.'
      . 'To install my extension, put the following line in LocalSettings.php:require_once "$IP/extensions/collate/collate.php \n";');
	die( -1 );
}

$messages = array();

$messages['en'] = array(
  'begincollate'                      => 'Collate Manuscript Pages',
  'collate-nopermission'              => 'You do not have permission to access the functionality of this page.',
  'collate-fewuploads'                => 'You need to upload at least 2 manuscripts to be able to collate manuscripts.',
  'collate-error-fewtexts'            => 'You should submit at least 2 manuscript pages in order to be able to collate something.',
  'collate-error-manytexts'           => 'You can only collate up to 10 single manuscript pages (single pages in collections are also counted).',
  'collate-error-collatex'            => 'Collatex Server is not running or not properly configured. To resolve this, start up collatex, or reconfigure the collatex URL in localsettings.php.',
  'collate-error-notexists'           => 'Something went wrong when retrieving the wiki page texts. One or more of your selected pages do not contain any texts.',
  'collate-error-database'            => 'Something went wrong when interacting with the database.',
  'collate-error-wikipage'            => 'Something went wrong when creating a new wiki page.',
  'collate-success'                   => 'Your collation was succesfull. The result in table format is displayed below.',
  'collate-tableread'                 => 'Red table entries indicate that text in this table row is not equal. Green table entries indicate that text in this table row is equal.',
  'collate-savetable'                 => 'You can choose to save this table. Saving this table will redirect you to a newly created page.',
  'collate-redirecthover'             => 'Redirects back to the starting page.',
  'collate-redirect'                  => 'Back to main page',
  'collate-savehover'                 => 'Saves this table on a separate page.',
  'collate-save'                      => 'Save this table',
  'collate-welcome'                   => 'Welcome to the collation module',
  'collate-about'                     => 'About the Collation Module',
  'collate-version'                   => 'Collation Module version: 0.01.',
  'collate-software'                  => 'Software used:',
  'collate-lastedit'                  => 'This page last edited by Root on 19/11/2015',
  'collate-instruction1'              => 'Below, all your uploaded manuscript pages are shown by title. You can collate up to 5 single manuscript pages by ticking the checkboxes of the manuscript pages you want to collate. A minimum of 2 manuscript pages can be collated.',
  'collate-instruction2'              => 'You can also collate collections. Note that collections that contain more than 9 pages will not appear in this list.',
  'collate-manuscriptpages'           => 'Manuscript Pages',
  'collate-collections'               => 'Collections',
  'collate-contains'                  => 'Contains:',
  'collate-hover'                     => 'Collate the selected texts.',
  'collate-submit'                    => 'Collate',
  'collate-newpage'                   => 'You can edit this page to add additional information. The table will still be displayed.',
  'collatehooks-nopermission'         => 'New collations can only be created on the Special:BeginCollate page',
  'collatehooks-nodeletepermission'   => 'You are not allowed to delete this page',
  'collatehooks-move'                 => 'You are not allowed to move collation pages',
  );

$messages['en-gb'] = $messages['en'];