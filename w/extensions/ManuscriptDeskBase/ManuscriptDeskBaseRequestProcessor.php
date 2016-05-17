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
class ManuscriptDeskBaseRequestProcessor {

    protected $request;
    protected $validator;

    public function __construct(WebRequest $request, ManuscriptDeskBaseValidator $validator = null) {
        $this->request = $request;
        $this->validator = $validator;
    }

    public function requestWasPosted() {
        if (!$this->request->wasPosted()) {
            return false;
        }
        
        return true;
    }

    /**
     * This function checks if the edit token was posted
     */
    public function tokenWasPosted() {
        $edit_token = $this->getEditToken();
        if ($edit_token === '') {
            return false;
        }

        return true;
    }

    /**
     * This function gets the edit token
     */
    protected function getEditToken() {
        return $this->request->getText('wpEditToken');
    }

    /**
     * This function checks the edit token
     */
    public function checkEditToken(User $user) {
        $edit_token = $this->getEditToken();
        if ($user->matchEditToken($edit_token) === false) {
            throw new \Exception('error-edittoken');
        }

        return true;
    }

    public function defaultPageWasPosted() {
        if ($this->request->getText('default_page_posted') !== '') {
            return true;
        }

        return false;
    }

    public function redirectBackPosted() {
        if ($this->request->getText('redirect_posted') !== '') {
            return true;
        }

        return false;
    }

    public function savePagePosted() {
        if ($this->request->getText('save_page_posted') === '') {
            return false;
        }

        return true;
    }
    
    public function getCollectionTitle() {
        $request = $this->request;
        $validator = $this->validator;
        return $validator->validateString($request->getText('collection_title'));
    }

}
