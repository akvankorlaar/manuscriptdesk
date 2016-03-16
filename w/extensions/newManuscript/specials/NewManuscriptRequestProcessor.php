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
 * 
 */
class NewManuscriptRequestProcessor extends ManuscriptDeskBaseRequestProcessor {

    public function loadUploadFormData() {
        $request = $this->request;
        if (!$request->getCheck('wpUpload')) {
            throw new \Exception('error-request');
        }

        $posted_manuscript_title = $this->getPostedManuscriptTitle();
        $posted_collection = $this->getPostedCollectionTitle();
        $selected_collection = $this->getSelectedCollection();
        return array($posted_manuscript_title, $posted_collection, $selected_collection);
    }

    private function getPostedManuscriptTitle() {
        $request = $this->request;
        $validator = $this->validator;
        $posted_manuscript_title = $validator->validateString($request->getText('wptitle_field'));
    }

    private function getPostedCollectionTitle() {
        $request = $this->request;
        $validator = $this->validator;
        if ($request->getText('wpcollection_field') === '') {
            return 'none';
        }

        $posted_manuscript_title = $validator->validateString($request->getText('wpcollection_field'));
    }

    private function getSelectedCollection() {
        $request = $this->request;
        $validator = $this->validator;
        if ($request->getText('selected_collection') === '') {
            return '';
        }

        return $validator->validateString($request->getText('selected_collection'));
    }

}
