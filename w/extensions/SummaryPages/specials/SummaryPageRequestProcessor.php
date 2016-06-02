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
class SummaryPageRequestProcessor extends ManuscriptDeskBaseRequestProcessor {

    /**
     * Get request values if user wants to see data for a single letter or number 
     */
    public function getLetterOrButtonRequestValues($lowercase_alphabet) {

        $request = $this->request;
        $validator = $this->validator;
        $offset = null;

        foreach ($request->getValueNames()as $value) {

            if (in_array($value, $lowercase_alphabet)) {
                $button_name = strval($value);
            }
            elseif ($value === 'offset') {
                $offset = (int) $validator->validateStringNumber($request->getText($value));

                if ($offset < 0) {
                    throw new \Exception('error-request');
                }
            }
        }

        if (!isset($button_name)) {
            throw new \Exception('error-request');
        }

        return array($button_name, $offset);
    }

    public function singleCollectionPosted() {
        if ($this->request->getText('single_collection_posted') !== '') {
            return true;
        }

        return false;
    }

}
