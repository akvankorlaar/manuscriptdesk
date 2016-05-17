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
class HelperScriptsRequestProcessor extends ManuscriptDeskBaseRequestProcessor {

    public function buttonDeleteManuscriptsPosted() {
        $request = $this->request;
        $validator = $this->validator;

        if ($request->getText('delete_manuscripts_posted') !== '') {
            return true;
        }

        false;
    }

    public function deletePhrasePosted() {
        global $wgHelperScriptsOptions;

        $request = $this->request;
        $validator = $this->validator;

        if ($request->getText('phrase_posted') !== '') {
            $this->checkDeleteAvailable($wgHelperScriptsOptions['delete_available']);
            $this->checkIpAddress($wgHelperScriptsOptions['deleter_ip']);
            $this->checkPhrase($wgHelperScriptsOptions['deleter_passphrase']);
            return true;
        }

        return false;
    }

    private function checkDeleteAvailable($option) {
        if ($option === 'on') {
            return;
        }

        throw new \Exception('error-request');
    }

    private function checkIpAddress($allowed_ip) {
        $request = $this->request;
        $current_ip = $request->getIP();

        if ($allowed_ip === $current_ip) {
            return;
        }

        throw new \Exception('error-request');
    }

    private function checkPhrase($passphrase) {
        $request = $this->request;
        $posted_phrase = $request->getText('wpphrase');

        if ($passphrase === $posted_phrase) {
            return;
        }

        throw new \Exception('error-request');
    }

}
