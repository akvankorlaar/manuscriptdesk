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
if (!defined('MEDIAWIKI')) {
    echo('This is an extension to the MediaWiki package and cannot be run standalone.'
    . 'To install my extension, put the following line in LocalSettings.php:require_once "$IP/extensions/newManuscript/newManuscript.php \n";');
    die(-1);
}

$messages = array();

$messages['en'] = array(
  'stylometricanalysis' => 'Stylometric Analysis',
  'stylometricanalysis-desc' => 'This extension permits users to perform Stylometric Analysis on texts for the Manuscript Desk.',
  'stylometricanalysis-welcome' => 'Welcome to the Stylometric Analysis module',
  'stylometricanalysis-about' => 'About the Stylometric Analysis module',
  'stylometricanalysis-version' => 'StylometricAnalysis Module version: 0.01',
  'stylometricanalysis-software' => 'Software used: ',
  'stylometricanalysis-lastedit' => 'This page was last edited by Root on 05/02/2016',
  'stylometricanalysis-instruction1' => 'Below, all your collections that contain at least 5 pages are displayed. A minimum of 2 and a maximum of 5 collections can be selected for the analysis.',
  'stylometricanalysis-collectionheader' => 'Collections',
  'stylometricanalysis-wordformheader' => 'Enter Words (Optional)',
  'stylometricanalysis-contains' => 'Contains:',
  'stylometricanalysis-submit' => 'Submit',
  'stylometricanalysis-hover' => 'Submit current selection',
  'stylometricanalysis-options' => 'Stylometric Analysis Options',
  'stylometricanalysis-chosencollections' => 'You have chosen the following collections for the analysis:',
  'stylometricanalysis-chosencollection2' => 'You can select additional options below.',
  'stylometricanalysis-output' => 'Stylometric Analysis Output',
  'stylometricanalysis-savevalue' => 'Click to save the current analysis',
  'stylometricanalysis-savetitle' => 'Save Current Analysis',
  'stylometricanalysis-error-manycollections' => 'You can only select up to 5 collections',
  'stylometricanalysis-error-fewcollections' => 'You need to select at least 2 collections',
  'stylometricanalysis-error-notexists' => 'Something went wrong when retrieving the wiki page texts.',
  'stylometricanalysis-error-request' => 'Unfortunately there was an error processing your request',
  'stylometricanalysis-error-toosmall' => 'One of the collections you have selected does not have enough text to support an analysis',
  'stylometricanalysis-error-minsize' => 'One of your collections is smaller than the minimum size specified in the form',
  'stylometricanalysis-error-segmentsize' => 'One of your collections is too small to support the entered segment size and/or stepsize',
  'stylometricanalysis-error-ngramsize' => 'One of your collections is too small to support the entered ngram size',
  'stylometricanalysis-error-internal' => 'There was an internal error. Please contact the administrator',
  'stylometricanalysis-error-database' => 'A database error occurred. Please contact the administrator',
  //form field names for Form2
  '-stylometricanalysis-section-import' => 'Import Settings',
  '-stylometricanalysis-section-preprocess' => 'Preprocess Settings',
  '-stylometricanalysis-section-feature' => 'Feature Settings',
  '-stylometricanalysis-section-visualization' => 'Visualization',
);

$messages['en-gb'] = $messages['en'];
