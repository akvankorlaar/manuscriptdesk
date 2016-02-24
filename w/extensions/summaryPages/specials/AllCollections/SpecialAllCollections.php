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
class SpecialAllCollections extends SummaryPageBase {

    private $page_name = 'AllCollections';

    public function __construct() {

        parent::__construct($this->page_name);
    }

    protected function getViewer() {
        return new AllCollectionsViewer($this->getOutput());
    }

    protected function getWrapper() {
        return new AllCollectionsWrapper();
    }
    
    protected function getFormDataGetter(){
        return new SummaryPageFormDataGetter($this->getRequest(), new ManuscriptDeskBaseValidator());
    }

    protected function getSpecialPageName() {
        return $this->page_name;
    }

}
