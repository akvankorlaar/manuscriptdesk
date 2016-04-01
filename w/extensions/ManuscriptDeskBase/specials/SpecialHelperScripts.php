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
        parent::__construct('SpecialHelperScripts');
    }

    public function execute() {

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
    }

    private function processDefaultPage() {
        $button_name = $this->request_processor->getDefaultPageData();
        
        if($button_name === 'update_alphabetnumbers_posted'){
            $this->updateAlphabetNumbersTable();
        }else{
                //script that can automatically delete all manuscript pages.. should be very safe--> put in some large authentication code
        }
    
    }
    
    private function updateAlphabetNumbersTable(){
        
        $this->wrapper->updateAlphabetNumbersCollections();
  
    }

    protected function setViewer() {

        if (isset($this->viewer)) {
            return;
        }

        return $this->viewer = new HelperScriptsViewer($this->getOutput());
    }

    protected function setWrapper() {

        if (isset($this->wrapper)) {
            return;
        }

        return $this->wrapper = new HelperScriptsWrapper();
    }

    protected function setRequestProcessor() {

        if (isset($this->request_processor)) {
            return;
        }

        return $this->request_processor = new HelperScriptsRequestProcessor($this->getRequest(), new ManuscriptDeskBaseValidator());
    }

}
