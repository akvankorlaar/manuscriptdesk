<?php

/**
 * This file is part of the newManuscript extension
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
class SpecialSingleManuscriptPages extends SummaryPageBase {

    private $page_name = 'SingleManuscriptPages';

    public function __construct() {
        parent::__construct($this->page_name);
    }

    public function setViewer($object = null) {
        
        if(isset($this->viewer)){
            return;
        }
        
        $viewer = ObjectRegistry::getInstance()->getSingleManuscriptPagesViewer($this->getOutput());
        $viewer->setPageName($this->page_name);
        return $this->viewer = $viewer;
    }

    public function setWrapper($object = null) {
        
        if(isset($this->wrapper)){
            return;
        }
        
        return $this->wrapper = ObjectRegistry::getInstance()->getSingleManuscriptPagesWrapper();
    }

    public function setRequestProcessor() {
        
        if(isset($this->request_processor)){
            return;
        }
        
        return $this->request_processor = ObjectRegistry::getInstance()->getSummaryPageRequestProcessor($this->getRequest());
    }

    protected function getSpecialPageName() {
        return $this->page_name;
    }

}
