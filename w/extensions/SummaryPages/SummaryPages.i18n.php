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

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
// Check environment
if ( !defined( 'MEDIAWIKI' ) ) {
	echo('This is an extension to the MediaWiki package and cannot be run standalone.'
      . 'To install my extension, put the following line in LocalSettings.php:require_once "$IP/extensions/collate/collate.php \n";');
	die( -1 );
}

$messages = array();

$messages['en'] = array(  
  'allcollations'                                 => 'All Collations',
  'allcollations-instruction'                     => 'Click one of the buttons to view all collations starting with the letter of your choice.',
  'allcollations-nocollations'                    => 'There are no collations that begin with this letter yet.',
  'allcollations-nocollations-number'             => 'There are no collations that begin with this number yet.',
  'allstylometricanalysis-instruction'            => 'All StylometricAnalyses',
  'allstylometricanalysis-nostylometricanalysis'  => 'There are no stylometricanalyses that begin with this letter yet.',
  'allstylometricanalysis-nostylometricanalysis-number' => 'There are no stylometricanalyses that begin with this number yet.',
  'allstylometricanalysis'                        => 'All Stylometricanalyses',
  'singlemanuscriptpages'                         => 'Single Manuscript Pages',
  'singlemanuscriptpages-nomanuscripts'           => 'There are no manuscript pages without collection that begin with this letter yet.',
  'singlemanuscriptpages-nomanuscripts-number'    => 'There are no manuscript pages without collection that begin with this number yet.',
  'singlemanuscriptpages-previoushover'           => 'Go to the previous page',
  'singlemanuscriptpages-previous'                => 'Back to previous page',
  'singlemanuscriptpages-nexthover'               => 'Go to the next page',
  'singlemanuscriptpages-next'                    => 'Go to next page',
  'singlemanuscriptpages-instruction'             => 'Click one of the buttons to view single manuscript pages (not belonging to a collection) starting with the letter of your choice.',
  'allcollections'                                => 'All Collections',
  'allcollections-nocollections'                  => 'There are no collections that begin with this letter yet.',
  'allcollections-nocollections-number'           => 'There are no collections that begin with this number yet.',
  'allcollections-instruction'                    => 'Click one of the buttons to view all collections starting with the letter of your choice.',
  'userpage-welcome'                              => 'Welcome',
  'userpage-error-empty'                          => 'Please fill in a new title for your manuscript page',
  'userpage-error-log1'                           => 'SpecialUserPage failed to delete page when changing title. New stray title at',
  'userpage-error-log2'                           => 'SpecialUserPage failed to update manuscripts table when changing title. New stray title at',
  'userpage-error-log3'                           => '. Url the user wanted to change:',
  'userpage-error-editmax1'                       => 'You can only use a maximum of',
  'userpage-error-editmax2'                       => 'charachters for the input',
  'userpage-error-editmax3'                       => 'charachters for the notes. You have currently used',
  'userpage-error-editmax4'                       => 'charachters.',
  'userpage-error-wikipage'                       => 'SpecialUserPage failed to create a new page:',
  'userpage-error-wikipage2'                      => 'Something went wrong when creating the new page',
  'userpage-error-exists'                         => 'This title already exists. Please specify another title',
  'userpage-error-alphanumeric'                   => 'You can only use letters or numbers for the input',
  'userpage-error-alphanumeric2'                  => "You can only use letters, numbers, or these charachters: '-./:' for the websource textfield",
  'userpage-error-alphanumeric3'                  => "You can only use letters, numbers, or these charachters: '.,!?' for the notes",
  'userpage-editcomplete'                         => 'Your collection metadata has been edited. Note that it is possible that you do not see results of this edit on the page immediately, because in some cases your browser caches the pages.',
  'userpage-linkback1'                            => 'Go back to the Manuscript Page',
  'userpage-linkback2'                            => 'Go back to ',
  'userpage-editmetadata'                         => 'Editing metadata for',
  'userpage-newmanuscripttitle'                   => 'New Manuscript Page Title',
  'metadata-title'                                => 'Collection Title',
  'metadata-author'                               => 'Author Name',
  'metadata-year'                                 => 'Published in year',
  'metadata-pages'                                => 'Number of Pages',
  'metadata-category'                             => 'Category',
  'metadata-produced'                             => 'Produced in Year',
  'metadata-producer'                             => 'Producer',
  'metadata-editors'                              => 'Editors',
  'metadata-journal'                              => 'Journal',
  'metadata-journalnumber'                        => 'Journal Number',
  'metadata-translators'                          => 'Translators',
  'metadata-websource'                            => 'Web(source)',
  'metadata-id'                                   => 'ID Number',
  'metadata-notes'                                => 'Notes',
  'metadata-submit'                               => 'Submit Edit',
  'userpage-editmetadatabutton'                   => 'Edit Metadata',
  'userpage-newcollection'                        => 'Add a new page to this collection',
  'userpage-metadata'                             => 'Metadata',
  'userpage-contains'                             => 'This collection contains',
  'userpage-contains2'                            => 'single manuscript page(s).',
  'userpage-optional'                             => 'Every field is optional.',
  'userpage-tabletitle'                           => 'Title',
  'userpage-collection'                           => 'Collection',
  'userpage-user'                                 => 'User',
  'userpage-goback'                               => 'Go Back',
  'userpage-edittitleinstruction'                 => 'You can specify a new title for your manuscript page below. Keep in mind that changing the title may also change the ordering of the manuscript pages within the collection, because the pages are alphabetically ordered.',
  'userpage-edittitle'                            => 'Editing Title of Manuscript Page',
  'userpage-creationdate'                         => 'Creation Date',
  'userpage-changetitle'                          => 'Change Title',
  'userpage-newmanuscriptpage'                    => 'Create a new manuscript page',
  'userpage-newcollation'                         => 'Create a new collation',
  'userpage-newcollection'                        => 'Create a new collection',
  'userpage-manuscriptinstr'                      => 'Below are all your uploaded manuscript pages that are not part of a collection.',
  'userpage-nomanuscripts'                        => 'You have not uploaded any manuscripts yet.',
  'userpage-nocollations'                         => 'You have not collated any manuscripts yet.',
  'userpage-nocollections'                        => 'You have not created any collections yet.',
  'userpage-nostylometricanalysis'                => 'You have not created any stylometric analyses yet.',  
  'userpage-newstylometricanalysis'               => 'Create a new stylometric analysis',
  'userpage-mymanuscripts'                        => 'Single Manuscript Pages',
  'userpage-mycollations'                         => 'My Collations',
  'userpage-mycollections'                        => 'My Collections',
  'userpage-mystylometricanalysis'                => 'My Stylometricanalyses',  
  'userpage-admin1'                               => 'Welcome admin. There are still',
  'userpage-admin2'                               => 'bytes left on disk, or',
  'userpage-admin3'                               => 'mb, or',
  'userpage-admin4'                               => 'gb',
  'userpage-signature'                            => 'Signature',  
  'recentmanuscriptpages'                         => 'Recent Manuscript Pages',
  'recentmanuscriptpages-nomanuscripts'           => 'No manuscript pages have been created yet.',
  'recentmanuscriptpages-information'             => 'This page shows the 30 most recently created manuscript pages.',
);

$messages['en-gb'] = $messages['en'];