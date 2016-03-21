<?php

/**
 * This file is part of the NewManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 * 
 */
class NewManuscriptPaths {

    private $posted_manuscript_title;
    private $user_name;
    private $initial_upload_full_path;
    private $base_export_path;
    private $user_export_path;
    private $full_export_path;
    private $extension;
    private $new_page_partial_url;
    private $image_uploaded = false;

    public function __construct($user_name, $posted_manuscript_title, $extension = '') {
        $this->user_name = $user_name;
        $this->posted_manuscript_title = $posted_manuscript_title;
        $this->extension = $extension;
    }

    public function setInitialUploadFullPath() {
        $extension = $this->extension;
        $posted_manuscript_title = $this->posted_manuscript_title;
        $initial_upload_base_path = $this->getInitialUploadBasePath();
        $this->makeDirectoryIfItDoesNotExist($initial_upload_base_path);
        $initial_upload_full_path = $initial_upload_base_path . '/' . $posted_manuscript_title . '.' . $extension;

        if (file_exists($initial_upload_full_path)) {
            //following error will only trigger if somehow an earlier attempt with this title did not complete (yet). In the case this error triggers, it means
            //that the initial upload exists, but there is no corresponding wiki page (yet)
            throw new \Exception('newmanuscript-error-page');
        }

        return $this->initial_upload_full_path = $initial_upload_full_path;
    }

    public function initialUploadFullPathIsConstructableFromScan() {
        $initial_upload_base_path = $this->getInitialUploadBasePath();
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

    private function getImageFileNameFromScan($path) {
        $file_scan = scandir($path);
        return isset($file_scan[2]) ? $file_scan[2] : "";
    }

    private function getInitialUploadBasePath() {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $posted_manuscript_title = $this->posted_manuscript_title;
        $user_name = $this->user_name;
        $initial_upload_base_path = $wgWebsiteRoot . '/' . $wgNewManuscriptOptions['original_images_dir'] . '/' . $user_name . '/' . $posted_manuscript_title;
        return $initial_upload_base_path;
    }

    private function setBaseExportPath() {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $base_export_path = $wgWebsiteRoot . '/' . $wgNewManuscriptOptions['zoomimages_root_dir'];

        $this->directoryShouldExist($base_export_path);

        return $this->base_export_path = $base_export_path;
    }

    private function setUserExportPath() {

        $user_export_path = $this->getBaseExportPath() . '/' . $this->user_name;

        $this->makeDirectoryIfItDoesNotExist($user_export_path);

        return $this->user_export_path = $user_export_path;
    }

    private function setFullExportPath() {

        $full_export_path = $this->getUserExportPath() . '/' . $this->posted_manuscript_title . '/';

        return $this->full_export_path = $full_export_path;
    }

    public function setExportPaths() {
        $this->setBaseExportPath();
        $this->setUserExportPath();
        $this->setFullExportPath();
        return;
    }

    public function moveUploadToInitialUploadDir($temp_path) {
        $initial_upload_dir_path = $this->getInitialUploadFullPath();
        $upload_succesfull = move_uploaded_file($temp_path, $initial_upload_dir_path);

        if (!$upload_succesfull) {
            wfErrorLog($this->msg('newmanuscript-error-upload') . "\r\n", $web_root . '/' . 'ManuscriptDeskDebugLog.log');
            throw new \Exception('newmanuscript-error-upload');
        }

        return $this->image_uploaded = true;
    }

    public function setNewPagePartialUrl() {
        global $wgNewManuscriptOptions;
        $manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
        return $this->new_page_partial_url = $manuscripts_namespace_url . $this->user_name . '/' . $this->posted_manuscript_title;
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

    public function getInitialUploadFullPath() {

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
        global $wgNewManuscriptOptions;
        return $wgNewManuscriptOptions['perl_path'];
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
    public function getWebLinkInitialUploadPath() {

        global $wgNewManuscriptOptions;

        $original_images_dir = $wgNewManuscriptOptions['original_images_dir'];
        $creator_user_name = $this->user_name;
        $manuscripts_title = $this->posted_manuscript_title;

        $initial_upload_base_path = $this->getInitialUploadBasePath();
        $image_file = $this->getImageFileNameFromScan($initial_upload_base_path);

        return '/' . $original_images_dir . '/' . $creator_user_name . '/' . $manuscripts_title . '/' . $image_file;
    }
    
    public function getWebLinkExportPath(){
        global $wgNewManuscriptOptions;
        
        return '/' . $wgNewManuscriptOptions['zoomimages_root_dir'] . '/' . $this->user_name . '/' . $this->posted_manuscript_title . '/';     
    }

    public function getNewPagePartialUrl() {
        if (!isset($this->new_page_partial_url)) {
            throw new \Exception('error-request');
        }

        return $this->new_page_partial_url;
    }

    public function imageUploaded() {
        return $this->image_uploaded;
    }

    /**
     * Delete all exported files in case something went wrong 
     */
    public function deleteSlicerExportFiles() {
        $this->deleteSliceDirectory();
        $this->deleteFullExportPathFiles();
        return;
    }

    private function deleteSliceDirectory() {
        $slice_directory = $this->getUserExportPath() . '/' . 'slice';

        //check if the temporary directory 'slice' exists. If it does, it should be deleted. 
        if (file_exists($slice_directory)) {
            $this->recursiveDeleteFromPath($slice_directory);
        }

        return;
    }

    private function deleteFullExportPathFiles() {
        $full_export_path = $this->getFullExportPath();
        $tile_group_url = $full_export_path . '/' . 'TileGroup0';
        $image_properties_url = $full_export_path . '/' . 'ImageProperties.xml';

        if (!is_dir($tile_group_url) || !is_file($image_properties_url)) {
            return;
        }

        return $this->recursiveDeleteFromPath($full_export_path);
    }

    public function deleteInitialUploadFullPath() {
        $initial_upload_full_path = $this->getInitialUploadFullPath();

        if (!$this->isAllowedImage($initial_upload_full_path)) {
            return;
        }
        
        $initial_upload_base_path = $this->getInitialUploadBasePath();

        return $this->recursiveDeleteFromPath($initial_upload_base_path);
    }

    /**
     * This function checks if the file is an image. This has been done earlier and more thouroughly when uploading, but these checks are just to make sure
     */
    private function isAllowedImage($path) {

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

    private function recursiveDeleteFromPath($path) {

        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                //recursive call
                $this->recursiveDeleteFromPath(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }
        else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

}
