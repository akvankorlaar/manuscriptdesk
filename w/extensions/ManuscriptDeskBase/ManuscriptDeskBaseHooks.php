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
abstract class ManuscriptDeskBaseHooks {
    
    /**
     * Classes that extend this class are intended to be called only when MediaWiki calls certain hooks. See https://www.mediawiki.org/wiki/Manual:Hooks 
     */

    protected $wrapper;
    protected $signature;
    protected $user_has_view_permission = false;

    public function __construct(ManuscriptDeskBaseWrapper $wrapper) {
        $this->wrapper = $wrapper;
    }

    protected function userIsAllowedToViewThePage(User $user) {
        if ($this->signature === 'public') {
            return true;
        }
        elseif ($this->signature === 'private' && $this->currentUserIsAManuscriptEditor($user)) {
            return true;
        }
        else {
            return false;
        }
    }

    private function currentUserIsAManuscriptEditor(User $user) {
        if (!in_array('ManuscriptEditors', $user->getGroups())) {
            return false;
        }

        return true;
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
            throw new \Exception('Invalid Object passed to' . __METHOD__);
        }
    }

    protected function currentPageExists(WikiPage $wikiPage) {
        $title_object = $wikiPage->getTitle();

        if ($title_object->exists()) {
            return true;
        }

        return false;
    }

    protected function savePageWasRequested(User $user) {
        $request = $user->getRequest();
        $request_processor = new ManuscriptDeskBaseRequestProcessor($request);

        if (!$request_processor->checkEditToken($user) || !$request_processor->savePagePosted()) {
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

    /**
     * Includes the unit tests into the unit test list
     */
    public function onUnitTestsList(&$files) {
        $files = array_merge($files, glob(__DIR__ . '/tests/*Test.php'));
        return true;
    }

}
