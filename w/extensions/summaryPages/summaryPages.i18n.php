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
  'allcollations-title'                           => 'All Collations',
  'allcollations-instruction'                     => 'Click one of the buttons to view all collations starting with the letter of your choice.',
  'allcollations-nocollations'                    => 'There are no collations that begin with this letter yet.',
  'allcollations-nocollations-number'             => 'There are no collations that begin with this number yet.',
  'allcollations-previoushover'                   => 'Go to the previous page',
  'allcollations-previous'                        => 'Back to previous page',
  'allcollations-nexthover'                       => 'Go to the next page',
  'allcollations-next'                            => 'Go to next page',
  'allcollations-created'                         => 'Created by:',
  'allcollations-on'                              => 'On:',  
  'allmanuscriptpages-title'                      => 'All Manuscript Pages',
  'allmanuscriptpages-nomanuscripts'              => 'There are no manuscript pages that begin with this letter yet.',
  'allmanuscriptpages-nomanuscripts-number'       => 'There are no manuscript pages that begin with this number yet.',
  'allmanuscriptpages-previoushover'              => 'Go to the previous page',
  'allmanuscriptpages-previous'                   => 'Back to previous page',
  'allmanuscriptpages-nexthover'                  => 'Go to the next page',
  'allmanuscriptpages-next'                       => 'Go to next page',
  'allmanuscriptpages-created'                    => 'Created by:',
  'allmanuscriptpages-on'                         => 'On:',
  'allmanuscriptpages-instruction'                => 'Click one of the buttons to view all manuscript pages starting with the letter of your choice.',
  'allcollections-title'                          => 'All Collections',
  'allcollections-nocollections'                  => 'There are no collections that begin with this letter yet.',
  'allcollections-nocollections-number'           => 'There are no collections that begin with this number yet.',
  'allcollections-instruction'                    => 'Click one of the buttons to view all collections starting with the letter of your choice.',
  'userpage-welcome'                              => 'Welcome',
  'userpage-nomanuscripts'                        => 'You have not uploaded any manuscripts yet.',
  'userpage-nocollations'                         => 'You have not collated any manuscripts yet.',
  'userpage-nocollections'                        => 'You have not created any collections yet.',
  'userpage-created'                              => 'Created on:',
  'userpage-mymanuscripts'                        => 'My Manuscript Pages',
  'userpage-mycollations'                         => 'My Collations',
  'userpage-mycollections'                        => 'My Collections',
  'userpage-admin1'                               => 'Welcome admin. There are still',
  'userpage-admin2'                               => 'bytes left on disk, or',
  'userpage-admin3'                               => 'mb, or',
  'userpage-admin4'                               => 'gb',
  'recentmanuscriptpages-title'                   => 'Recent Manuscript Pages',
  'recentmanuscriptpages-nomanuscripts'           => 'No manuscript pages have been created yet.',
  'recentmanuscriptpages-information'             => 'This page shows the 30 most recently created manuscript pages.',
  );

$messages['en-gb'] = $messages['en'];