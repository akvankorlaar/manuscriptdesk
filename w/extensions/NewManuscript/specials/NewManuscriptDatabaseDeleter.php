<?php

/**
 * This file is part of the Collate extension
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
class NewManuscriptDatabaseDeleter {

    private $wrapper;
    private $partial_url;
    private $collection_title;

    public function __construct(NewManuscriptWrapper $wrapper, $partial_url, $collection_title) {
        $this->wrapper = $wrapper;
        $this->partial_url = $partial_url;
        $this->collection_title = $collection_title;
    }
    
    public function execute(){
        $this->subtractAlphabetNumbersTable();
        $this->deleteDatabaseEntries();
    }

    private function deleteDatabaseEntries() {
        $status = $this->wrapper->deleteFromManuscripts($this->partial_url);

        if ($this->collection_title !== 'none') {
            $this->wrapper->checkAndDeleteCollectionifNeeded($this->collection_title);
        }

        return;
    }

    private function subtractAlphabetNumbersTable() {
        $main_title_lowercase = $this->wrapper->getManuscriptsLowercaseTitle($this->partial_url);
        $alphabetnumbes_context = $this->wrapper->determineAlphabetNumbersContextFromCollectionTitle($this->collection_title);
        $this->wrapper->subtractAlphabetNumbers($main_title_lowercase, $alphabetnumbes_context);
        return;
    }

}
