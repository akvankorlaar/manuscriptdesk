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
abstract class ManuscriptDeskImageApi extends SpecialPage {

    /**
     * Intention of classes that extend this class is to provide additional security for images uploaded to the Manuscript Desk by making sure that
     * users are logged inbefore they are allowed to view images. Images are not stored on the website root, and this class constructs
     * the path to this location when the user is logged in
     * 
     * arguments sent to this page by HTTP GET 
     * image=User/manuscript in case of SpecialOriginalImages
     * image=User/Manuscript/TileGroup/0-0-0.jpg in case of SpecialZoomImages
     * image=User/svgimagename in case of SpecialStylometricAnalysisImages
     */
    protected $arguments;

    /**
     * path of the image on disk 
     */
    protected $file_path;

    public function __construct($page_name) {
        parent::__construct($page_name);
    }

    public function execute($subpage_args) {
        try {
            $this->checkUserIsAllowedToViewFile();
            $this->checkPageArguments();
            $this->preventMediaWikiFromOutputtingSkin();
            $this->constructFilePath();
            $this->showFile();
            return true;
        } catch (Exception $e) {
            return $this->ShowFileNotFound();
        }
    }

    protected function checkUserIsAllowedToViewFile() {
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

    protected function checkPageArguments() {
        $request = $this->getRequest();
        $this->arguments = $image_arguments = $request->getText('image');
        $validator = ObjectRegistry::getInstance()->getManuscriptDeskBaseValidator();
        $validator->validateStringUrl($image_arguments);
        return;
    }

    protected function preventMediaWikiFromOutputtingSkin() {
        $out = $this->getOutput();
        $out->setArticleBodyOnly(true);
        return;
    }

    protected function ShowFileNotFound() {
        $response = $this->getRequest()->response();
        $response->header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
        return;
    }

    /**
     * Construct the file path for the image 
     */
    abstract protected function constructFilePath();

    /**
     * Output the file to the browser 
     */
    abstract protected function showFile();
}
