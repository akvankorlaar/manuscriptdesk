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
  'error-edittoken' => 'The edit token is not ok',
  'error-nopmerission' => 'You do not have permission to access the functionality of this page',
  'error-newpage' => 'The new Wiki Page could not be created',
  'newpage' => 'You can add custom information to this page. The saved analysis will be preserved.'
);

$messages['en-gb'] = $messages['en'];