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
 * 
 */
class SpecialOriginalImages extends SpecialPage {

    /**
     * arguments sent to this page by HTTP GET (image=/user/manuscript)
     */
    private $image_arguments;

    /**
     * path of the image on disk 
     */
    private $image_path;

    public function __construct() {
        parent::__construct('OriginalImages');
    }

    public function execute($subpage_args) {
        try {
            $this->checkUserIsAllowedToViewImage();
            $this->checkImageArguments();
            $this->constructImageFilePath();
            $this->preventMediaWikiFromOutputtingSkin();
            $this->showImage();
            return true;
        } catch (Exception $e) {
            $message = $this->msg($e->getMessage());
            return $this->getOutput()->addHTML($message);
        }
    }

    private function checkUserIsAllowedToViewImage() {
        $user = $this->getUser();

        if (!in_array('ManuscriptEditors', $user->getGroups())) {
            throw new \Exception('error-nopermission');
        }

        $edit_token = $user->getEditToken();
        if ($user->matchEditToken($edit_token) === false) {
            throw new \Exception('error-nopermission');
        }

        return;
    }

    private function checkImageArguments() {
        $request = $this->getRequest();
        $this->image_arguments = $image_arguments = $request->getText('image');
        $validator = ObjectRegistry::getInstance()->getManuscriptDeskBaseValidator();
        $validator->validateStringUrl($image_arguments);
        return;
    }

    private function constructImageFilePath() {
        global $wgOriginalImagesPath;
        $image_arguments = $this->image_arguments;
        $partial_path = $wgOriginalImagesPath . $this->image_arguments;

        if (!is_dir($partial_path)) {
            throw new \Exception('error-request');
        }

        $file_scan = scandir($partial_path);

        if (!isset($file_scan[2]) || $file_scan[2] === "") {
            throw new \Exception('error-request');
        }

        return $this->image_path = $partial_path . DIRECTORY_SEPARATOR . $file_scan[2];
    }

    private function preventMediaWikiFromOutputtingSkin() {
        $out = $this->getOutput();
        $out->setArticleBodyOnly(true);
        return;
    }

    private function showImage() {
        if (!isset($this->image_path)) {
            throw new \Exception('error-request');
        }

        $response = $this->getRequest()->response();
        $response->header('Content-Type: image/png');
        readfile($this->image_path);
        return;
    }

}
