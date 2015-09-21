<?php
/**  
 * This file is part of the newManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 */

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
// Check environment
if ( !defined( 'MEDIAWIKI' ) ) {
	echo('This is an extension to the MediaWiki package and cannot be run standalone.'
      . 'To install my extension, put the following line in LocalSettings.php:require_once "$IP/extensions/newManuscript/newManuscript.php \n";');
	die( -1 );
}

$messages = array();

$messages['en'] = array( 
  'stylometricanalysis'                     => 'Stylometric Analysis',
  'stylometricanalysis-desc'                => 'This extension permits users to perform Stylometric Analysis on texts for the Manuscript Desk.',
  'stylometricanalysis-nopermission'        => 'You do not have permission to access the functionality of this page.',
  'stylometricanalysis-fewcollections'      => 'You need to create at least 2 collections to be able to do Stylometric Analysis.',
  'stylometricanalysis-welcome'             => 'Welcome to the Stylometric Analysis module',
  'stylometricanalysis-about'               => 'About the Stylometric Analysis module',
  'stylometricanalysis-collectionheader'    => 'Collections',
  'stylometricanalysis-wordformheader'      => 'Enter Words (Optional)',
  'stylometricanalysis-contains'            => 'Contains:',
  'stylometricanalysis-submit'              => 'Submit Selection',
  );

$messages['en-gb'] = $messages['en'];

