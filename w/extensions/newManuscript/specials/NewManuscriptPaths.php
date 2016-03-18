<?php

/**
 * This file is part of the newManuscript extension
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
    private $new_page_url; 
    private $image_uploaded = false;

    public function __construct($user_name, $posted_manuscript_title, $extension) {
        $this->user_name = $user_name;
        $this->posted_manuscript_title = $posted_manuscript_title;
        $this->extension = $extension;
    }

    public function setInitialUploadFullPath() {

        $extension = $this->extension;
        $posted_manuscript_title = $this->posted_manuscript_title;

        $initial_upload_base_path = $this->constructInitialUploadBasePath();
        $initial_upload_full_path = $initial_upload_base_path . DIRECTORY_SEPARATOR . $posted_manuscript_title . '.' . $extension;

        if (file_exists($initial_upload_full_path)) {
            //following error will only trigger if somehow an earlier attempt with this title did not complete (yet). In the case this error triggers, it means
            //that the initial upload exists, but there is no corresponding wiki page (yet)
            throw new \Exception('newmanuscript-error-page');
        }

        return $this->initial_upload_full_path = $initial_upload_full_path;
    }

    private function constructInitialUploadBasePath() {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $posted_manuscript_title = $this->posted_manuscript_title;
        $user_name = $this->user_name;
        $save_directory_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $wgNewManuscriptOptions['original_images_dir'] . DIRECTORY_SEPARATOR . $user_name . DIRECTORY_SEPARATOR . $posted_manuscript_title;

        if (is_dir($save_directory_path)) {
            throw new \Exception('error-request');
        }

        $this->makeNewDirectory($save_directory_path);
        return $save_directory_path;
    }

    public function setBaseExportPath() {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $base_export_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $wgNewManuscriptOptions['zoomimages_root_dir'];

        if (!is_dir($base_export_path)) {
            throw new \Exception('error-request');
        }

        return $this->base_export_path = $base_export_path;
    }

    public function setUserExportPath() {

        $user_export_path = $this->getBaseExportPath() . DIRECTORY_SEPARATOR . $this->user_name;

        if (!is_dir($user_export_path)) {
            $this->makeNewDirectory($user_export_path);
        }

        return $this->user_export_path = $user_export_path;
    }

    public function setFullExportPath() {

        $full_export_path = $this->getUserExportPath() . DIRECTORY_SEPARATOR . $posted_manuscript_title;

        if (is_dir($full_export_path)) {
            throw new \Exception('error-request');
        }

        return $this->full_export_path = $full_export_path;
    }

    public function moveUploadToInitialUploadDir($temp_path) {
        $initial_upload_dir_path = $this->getInitialUploadFullPath();
        $upload_succesfull = move_uploaded_file($temp_path, $initial_upload_dir_path);

        if (!$upload_succesfull) {
            wfErrorLog($this->msg('newmanuscript-error-upload') . "\r\n", $web_root . DIRECTORY_SEPARATOR . 'ManuscriptDeskDebugLog.log');
            throw new \Exception('newmanuscript-error-upload');
        }

        return $this->image_uploaded = true;
    }
    
    public function setNewPageUrl() {
        global $wgNewManuscriptOptions;
        $manuscripts_namespace_url = $wgNewManuscriptOptions['manuscripts_namespace'];
        $this->new_page_url = $manuscripts_namespace_url . $this->user_name . '/' . $this->posted_manuscript_title;
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

        $slicer_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $wgNewManuscriptOptions['slicer_path'];

        if (!file_exists($slicer_path)) {
            throw new \Exception('error-request');
        }

        return $slicer_path;
    }
    
    public function getNewPageUrl(){
        if(!isset($this->new_page_url)){
            throw new \Exception('error-request');
        }
        
        return $this->new_page_url;
    }

    public function imageUploaded() {
        return $this->image_uploaded;
    }
    
//    /**
//     * Delete all exported files in case something went wrong 
//     */
//    public function deleteExportFiles() {
//
//        $zoom_images_file = $this->full_export_path;
//        $slice_directory = $this->user_export_path . DIRECTORY_SEPARATOR . 'slice';
//
//        //check if the temporary directory 'slice' exists. If it does, it should be deleted. 
//        if (file_exists($slice_directory)) {
//            $this->deleteAllFiles($slice_directory);
//        }
//
//        $tile_group_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'TileGroup0';
//        $image_properties_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'ImageProperties.xml';
//
//        if (!is_dir($tile_group_url) || !is_file($image_properties_url)) {
//            return false;
//        }
//
//        return $this->deleteAllFiles($zoom_images_file);
//    }
//
//    /**
//     * The function recursively deleted all directories and files contained in $zoom_images_file
//     */
//    private function deleteAllFiles($zoom_images_file) {
//
//        if (is_dir($zoom_images_file) === true) {
//            $files = array_diff(scandir($zoom_images_file), array('.', '..'));
//
//            foreach ($files as $file) {
//                //recursive call
//                $this->deleteAllFiles(realpath($zoom_images_file) . DIRECTORY_SEPARATOR . $file);
//            }
//
//            return rmdir($zoom_images_file);
//        }
//        elseif (is_file($zoom_images_file) === true) {
//            return unlink($zoom_images_file);
//        }
//
//        return false;
//    }


}
