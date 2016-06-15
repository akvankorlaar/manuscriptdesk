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
class NewManuscriptPaths {

    private $posted_manuscript_title;
    private $user_name;
    private $initial_upload_full_path;
    private $base_export_path;
    private $user_export_path;
    private $full_export_path;
    private $extension;
    private $partial_url;
    private $image_uploaded = false;

    public function __construct($user_name, $posted_manuscript_title, $extension = '') {
        $this->user_name = $user_name;
        $this->posted_manuscript_title = $posted_manuscript_title;
        $this->extension = $extension;
    }

    /**
     * Set the path for the initial upload location of the manuscript images 
     */
    public function setOriginalImagesFullPath() {
        $extension = $this->extension;
        $posted_manuscript_title = $this->posted_manuscript_title;
        $initial_upload_base_path = $this->getOriginalImagesBasePath();
        $this->makeDirectoryIfItDoesNotExist($initial_upload_base_path);
        $initial_upload_full_path = $initial_upload_base_path . '/' . $posted_manuscript_title . '.' . $extension;

        if (file_exists($initial_upload_full_path)) {
            //following error will only trigger if somehow an earlier attempt with this title did not complete (yet). In the case this error triggers, it means
            //that the initial upload exists, but there is no corresponding wiki page (yet)
            throw new \Exception('newmanuscript-error-page');
        }

        return $this->initial_upload_full_path = $initial_upload_full_path;
    }

    /**
     * Scan the path for images to see whether an image that is allowed exists in the specified location
     */
    public function originalImagesFullPathIsConstructableFromScan() {
        $initial_upload_base_path = $this->getOriginalImagesBasePath();
        if (!is_dir($initial_upload_base_path)) {
            return false;
        }

        $image_file_name = $this->getImageFileNameFromScan($initial_upload_base_path);
        $initial_upload_full_path = $initial_upload_base_path . '/' . $image_file_name;

        if ($image_file_name === "" || !$this->isAllowedImage($initial_upload_full_path)) {
            return false;
        }

        $this->initial_upload_full_path = $initial_upload_full_path;

        return true;
    }

    /**
     * Scan the path and return a file in the expected location 
     */
    private function getImageFileNameFromScan($path) {
        $file_scan = scandir($path);
        return isset($file_scan[2]) ? $file_scan[2] : "";
    }

    /**
     * Get the base path for the initial upload 
     */
    public function getOriginalImagesBasePath() {        
        global $wgOriginalImagesPath;
        $posted_manuscript_title = $this->posted_manuscript_title;
        $user_name = $this->user_name;
        return $wgOriginalImagesPath . $user_name . '/' . $posted_manuscript_title;
    }

    /**
     * Set the base of the export path (location where sliced zoom images will be stored) 
     */
    private function setBaseExportPath() {
        global $wgZoomImagesPath; 
        $this->directoryShouldExist($wgZoomImagesPath);
        return $this->base_export_path = $wgZoomImagesPath;
    }

    private function setUserExportPath() {
        $user_export_path = $this->getBaseExportPath() . $this->user_name;
        $this->makeDirectoryIfItDoesNotExist($user_export_path);
        return $this->user_export_path = $user_export_path;
    }

    private function setFullExportPath() {
        $full_export_path = $this->getUserExportPath() . '/' . $this->posted_manuscript_title . '/';
        return $this->full_export_path = $full_export_path;
    }

    /**
     * Set the export paths (location where the zoom images will be stored) 
     */
    public function setExportPaths() {
        $this->setBaseExportPath();
        $this->setUserExportPath();
        $this->setFullExportPath();
        return;
    }

    /**
     * Move the uploaded image from the temporary location to the initial upload directory 
     */
    public function moveUploadToOriginalImagesDir($temp_path) {
        $initial_upload_dir_path = $this->getOriginalImagesFullPath();
        $upload_succesfull = move_uploaded_file($temp_path, $initial_upload_dir_path);

        if (!$upload_succesfull) {
            wfErrorLog($this->msg('newmanuscript-error-upload') . "\r\n", $web_root . '/' . 'ManuscriptDeskDebugLog.log');
            throw new \Exception('newmanuscript-error-upload');
        }

        return $this->image_uploaded = true;
    }

    /**
     * Set the partial URL (namespace:username/title) 
     */
    public function setPartialUrl() {
        $manuscripts_namespace_url = 'Manuscripts:';
        return $this->partial_url = $manuscripts_namespace_url . $this->user_name . '/' . $this->posted_manuscript_title;
    }

    private function makeDirectoryIfItDoesNotExist($path) {
        if (!is_dir($path)) {
            $this->makeNewDirectory($path);
        }

        return;
    }

    private function directoryShouldExist($path) {
        if (!is_dir($path)) {
            throw new \Exception('error-request');
        }

        return;
    }

    private function makeNewDirectory($path) {
        return mkdir($path, 0755, true);
    }

    public function getOriginalImagesFullPath() {

        if (!isset($this->initial_upload_full_path)) {
            throw new \Exception('error-request');
        }

        return $this->initial_upload_full_path;
    }

    public function getBaseExportPath() {

        if (!isset($this->base_export_path)) {
            throw new \Exception('error-request');
        }

        return $this->base_export_path;
    }

    public function getUserExportPath() {

        if (!isset($this->user_export_path)) {
            throw new \Exception('error-request');
        }

        return $this->user_export_path;
    }

    public function getFullExportPath() {

        if (!isset($this->full_export_path)) {
            throw new \Exception('error-request');
        }

        return $this->full_export_path;
    }

    public function getExtension() {

        if (!isset($this->extension)) {
            throw new \Exception('error-request');
        }

        return $this->extension;
    }

    public function getPerlPath() {
        global $wgPerlPath;
        return $wgPerlPath;
    }

    public function getSlicerPath() {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;

        $slicer_path = $wgWebsiteRoot . '/' . $wgNewManuscriptOptions['slicer_path'];

        if (!file_exists($slicer_path)) {
            throw new \Exception('error-request');
        }

        return $slicer_path;
    }

    /**
     * Construct the full path of the original image
     */
    public function getWebLinkOriginalImagesPath() {
        global $wgArticleUrl;
        $creator_user_name = $this->user_name;
        $manuscripts_title = $this->posted_manuscript_title;
        return $wgArticleUrl . 'Special:OriginalImages' . '?image=' . $creator_user_name . '/' . $manuscripts_title;
    }

    public function getWebLinkExportPath() {
        global $wgArticleUrl; 
        $creator_user_name = $this->user_name;
        $manuscripts_title = $this->posted_manuscript_title; 
        return $wgArticleUrl . 'Special:ZoomImages' . '?image=' . $creator_user_name . '/' . $manuscripts_title . '/';
    }

    public function getPartialUrl() {
        if (!isset($this->partial_url)) {
            throw new \Exception('error-request');
        }

        return $this->partial_url;
    }

    public function imageUploaded() {
        return $this->image_uploaded;
    }

    /**
     * Check if the file is an image with an allowed extension. This has been done earlier and more thouroughly when uploading, but these checks are just to make sure
     */
    public function isAllowedImage($path) {

        global $wgNewManuscriptOptions;

        $allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];

        if (pathinfo($path, PATHINFO_EXTENSION) !== null) {
            $extension = trim(pathinfo($path, PATHINFO_EXTENSION));

            if (getimagesize($path) === false) {
                return false;
            }

            foreach ($allowed_file_extensions as $allowed_extension) {
                if (strpos($extension, $allowed_extension) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

}
