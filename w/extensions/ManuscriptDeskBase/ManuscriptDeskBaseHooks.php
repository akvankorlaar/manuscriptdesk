<?php

/**
 * This file is part of the collate extension
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
abstract class ManuscriptDeskBaseHooks {

    public function __construct() {
        
    }

    protected function isInManuscriptsNamespace($object) {
        $namespace = $this->getNamespaceFromObject($object);

        if ($namespace !== NS_MANUSCRIPTS) {
            return false;
        }

        return true;
    }

    //probably needs instanceof when OutputPage is not available, or when Title is available directly
    protected function manuscriptPageExists(Outputpage $out) {
        if (!$out->getTitle()->exists()) {
            return false;
        }

        return true;
    }
    
    /**
     * Assert whether the current user is a sysop
     */
    protected function currentUserIsASysop(User $user) {
        $user_groups = $user->getGroups();
        if (!in_array('sysop', $user_groups)) {
            return false;
        }

        return true;
    }

    /**
     * @param type $object has to be instance of WikiPage, OutputPage or Title
     */
    protected function getNamespaceFromObject($object) {
        if ($object instanceof WikiPage || $object instanceof OutputPage) {
            return $object->getTitle()->getNamespace();
        }
        elseif ($object instanceof Title) {
            return $object->getNamespace();
        }
        else {
            throw Exception('Invalid Object passed to' . __METHOD__);
        }
    }

    protected function currentPageExists(WikiPage $wikiPage) {
        $title_object = $wikiPage->getTitle();

        if (!$title_object->exists()) {
            return false;
        }

        return true;
    }

    /**
     * This function retrieves the message from the i18n file for String $identifier
     */
    protected function getMessage($identifier) {
        return wfMessage($identifier)->text();
    }

}
