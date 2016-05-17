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
if (!defined('MEDIAWIKI')) {
    echo('This is an extension to the MediaWiki package and cannot be run standalone.'
    . 'To install my extension, put the following line in LocalSettings.php:require_once "$IP/extensions/ManuscriptDeskBase/ManuscriptDeskBase.php \n";');
    die(-1);
}

$messages = array();

$messages['en'] = array(
  'validation-notalphanumeric' => 'You can only enter alphanumeric charachters (a-z, A-Z, 0-9)',
  'validation-toolongstring' => 'Unusually long string length detected',
  'validation-morethanfiftycharachters' => 'You can only enter a maximum of 50 charachters in the form fields',
  'validation-notanumber' => 'You have entered a non-numeric charachter in one of the number fields',
  'validation-empty' => 'One of the form fields is empty',
  'validation-websourcecharachters' => 'The websource field can only contain alphanumeric charachters, whitespace, and' . '-./:' . 'charachters',
  'validation-noteslength' => 'Your notes contain too many charachters',
  'validation-notescharachters' => 'Your notes can only contain alphanumeric, whitespace and' . ',.;!?' . 'charachters',
  'validation-metadatacharachters' => 'Your metadata fields can only contain alphanumeric charachters and whitespace',
  'error-edittoken' => 'The edit token is not ok',
  'error-nopermission' => 'You do not have permission to access the functionality of this page because you are not a member of the ManuscriptEditors group.',
  'error-viewpermission' => 'You do not have permission to view the content of this page, because the owner of this page has made the content available only to the '
  . 'ManuscriptEditors group.',
  'error-newpage' => 'The new Wiki Page could not be created (perhaps because this page already exists)',
  'error-request' => 'Something went wrong when processing your request',
  'manuscriptdesk-newpage' => 'You can add custom information to this page. The saved analysis will be preserved.',
);

$messages['en-gb'] = $messages['en'];