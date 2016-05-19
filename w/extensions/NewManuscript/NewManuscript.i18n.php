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
 * 
 * This file incorporates work covered by the following copyright and
 * permission notice: 
 * 
 * Copyright (C) 2013 Richard Davis
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
 * @author Richard Davis <r.davis@ulcc.ac.uk>
 * @author Ben Parish <b.parish@ulcc.ac.uk>
 * @copyright 2013 Richard Davis
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
  'newmanuscript-nopermission'                    => 'You do not have permission to access the functionality of this page.',
  'newmanuscript-maxreached'                      => 'You have already uploaded the maximum amount of manuscript pages, and so you cannot upload more manucript pages unless you delete some of your earlier uploads',
  'newmanuscript-error-nofile'                    => 'Error: No file entered',
  'newmanuscript-error-noimage'                   => 'Error: File is not an image',
  'newmanuscript-error-page'                      => 'Error: You have already created a manuscript page with this title. Note that titles are not case sensitive',
  'newmanuscript-error-toolarge'                  => 'Error: The image file is too large',
  'newmanuscript-error-noextension'               => 'Error: Your file does not have an extension',
  'newmanuscript-error-fileformat'                => 'Error: You can only upload jpg or jpeg images',
  'newmanuscript-error-scripts'                   => 'Error: File may contain scripts',
  'newmanuscript-error-upload'                    => 'Error: Failed to create the upload file',
  'newmanuscript-error-database'                  => 'Error: Something went wrong when attempting to insert new data into the "manuscripts" table',
  'newmanuscript-error-notitle'                   => 'Error: No title entered',
  'newmanuscript-error-charachters'               => 'Error: You can only use letters or digits in your title name',
  'newmanuscript-error-toolong'                   => 'Error: Input title is too long',
  'newmanuscript-error-exists'                    => 'Error: You have already uploaded a page with this name',
  'newmanuscript-error-collectioncharachters'     => 'Error: You can only use letters or digits in your collection name',
  'newmanuscript-error-collectiontoolong'         => 'Error: Collection name is too long',
  'newmanuscript-error-collectionmaxreached'      => 'Error: This collection has already reached the maximum allowed manuscript pages',
  'newmanuscript-error-notcollectionsuser'        => 'Error: The collection you specified already exists, and you are not allowed to add pages to this collection because you did not create it',
  'newmanuscript-error-wikipage'                  => 'Error: The new wikipage could not be created',
  'slicer-error-importpath'                       => 'SlicerError: Import path of the initial upload does not exist',
  'slicer-error-exportpath'                       => 'SlicerError: Failed to make slicer export path',
  'slicer-error-upload'                           => 'SlicerError: You have already uploaded a file with this name',
  'slicer-error-slicerpath'                       => 'SlicerError: Path of the slicer does not exist',
  'slicer-error-execute'                          => 'SlicerError: The slicer encountered an error when executing the command',
  'newmanuscript'                                 => 'New Manuscript Page',
  'newmanuscript-title-instruction'               => 'Please enter the title for your manuscript page below. This title can only contain letters or digits.',
  'newmanuscript-collections-instruction'         => 'You can also add your new manuscript page to a collection. In this way you can link your manuscript page to other manuscript pages. This is optional. The collection title can also only contain letters or digits.',
  'newmanuscript-collections-instruction2'        => 'Note that pages within a collection will be linked in alphabetical order.', 
  'newmanuscript-collections'                     => 'Your current collection(s): ',
  'newmanuscript-submit'                          => 'Create New Manuscript Page',
  'upload-title'                                  => 'Description',
  'jbzv'                                          => 'JB ZV',
  'jbzv-descr'                                    => 'The JBZV Transcription Editor is designed to add an image in an iframe next to the edit form so that it can be transcribed using the edit box',
  'flash-viewer'                                  => 'Flash viewer',
  'javascript-viewer'                             => 'JavaScript viewer',
  'to-use-javascript'                             => 'To use the Javascript viewer',
  'to-use-flash'                                  => 'To use the Flash viewer',
  'click-here'                                    => 'click here',
  'instead'                                       => 'instead',
  'javascript-warning'                            => 'JavaScript must be enabled in order for you to use the BrainMaps API.</b>  However, it seems JavaScript is either disabled or not supported by your browser.   To view this page, enable JavaScript by changing your browser options, and then try again.',
  'error'                                         => 'The %s parameter is missing in the URL',
  'newmanuscripthooks-errorimage'                 => 'Original image is not available',
  'newmanuscripthooks-originalimage'              => 'Original Image',
  'newmanuscripthooks-nodeletepermission'         => 'You are not allowed to delete this page',
  'newmanuscripthooks-nopermission'               => 'New manuscript pages can only be created on the Special:newManuscript page',
  'newmanuscripthooks-maxchar1'                   => 'Your manuscript page already has more than the maximum allowed charachters. Your page currently has',
  'newmanuscripthooks-maxchar2'                   => 'charachters, while',
  'newmanuscripthooks-maxchar3'                   => 'charachters are allowed',
  'newmanuscripthooks-move'                       => 'You are not allowed to move manuscript pages',
  'newmanuscript-default'                         => 'This page has not been transcribed yet.',
  'newmanuscript-copyright'                       => 'NOTICE: the user is responsible for any copyright infringement of the images s/he uploads.',  
  );

$messages['en-gb'] = $messages['en'];