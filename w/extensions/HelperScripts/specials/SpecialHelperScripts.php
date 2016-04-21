<?php

/**
 * This file is part of the NewManuscript extension
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
class SpecialHelperScripts extends ManuscriptDeskBaseSpecials {

    public function __construct() {
        parent::__construct('HelperScripts');
    }

    public function execute($subpage_arguments) {

        try {
            $this->setVariables();
            $this->checkManuscriptDeskPermission();

            if (!$this->currentUserIsASysop()) {
                return true;
            }

            if ($this->request_processor->requestWasPosted()) {
                $this->processRequest();
                return true;
            }

            $this->getDefaultPage();
            return true;
        } catch (Exception $e) {
            $this->handleExceptions($e);
            return false;
        }
    }

    protected function getDefaultPage($error_message = '') {
        $this->viewer->showDefaultPage($error_message);
        return true;
    }

    protected function processRequest() {

        $request_processor = $this->request_processor;
        $request_processor->checkEditToken($this->getUser());

        if ($request_processor->defaultPageWasPosted()) {
            $this->processDefaultPage();
            return true;
        }

        if ($request_processor->deletePhrasePosted()) {
            $this->processDeleteManuscripts();
            return true;
        }

        throw new \Exception('error-request');
    }

    private function processDefaultPage() {
        if ($this->request_processor->buttonDeleteManuscriptsPosted()) {
            return $this->viewer->showDeletionForm();
        }
        else {
            $this->updateAlphabetNumbersTable();
            return $this->viewer->showActionComplete();
        }
    }

    private function updateAlphabetNumbersTable() {
        $wrapper = new AlphabetNumbersUpdater(new AlphabetNumbersWrapper());
        $wrapper->execute();
        return;
    }

    private function processDeleteManuscripts() {
        $wrapper = new HelperScriptsDeleteWrapper(new ManuscriptDeskDeleteWrapper($this->user_name, new AlphabetNumbersWrapper()));
        $wrapper->deleteManuscriptDeskData();
        $this->updateAlphabetNumbersTable();
        return $this->viewer->showActionComplete();
    }

    public function setViewer($object = null) {

        if (isset($this->viewer)) {
            return;
        }

        return $this->viewer = isset($object) ? $object : new HelperScriptsViewer($this->getOutput());
    }

    public function setWrapper($object = null) {
        //has to be determined at runtime
        return;
    }

    public function setRequestProcessor($object = null) {

        if (isset($this->request_processor)) {
            return;
        }

        return $this->request_processor = isset($object) ? $object : new HelperScriptsRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator());
    }

    /**
     * Callback function. Makes sure the page is redisplayed in case there was an error when entering the deletionform 
     */
    static function processInput($form_data) {
        return false;
    }

}
