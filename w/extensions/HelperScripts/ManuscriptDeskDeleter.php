<?php

/**
 * This file is part of the Collate extension
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
class ManuscriptDeskDeleter {

    private $wrapper;
    private $paths;
    private $collection_title;
    private $manuscripts_url; 

    public function __construct(ManuscriptDeskDeleteWrapper $wrapper, NewManuscriptPaths $paths, $collection_title, $manuscripts_url = null) {
        $this->wrapper = $wrapper;
        $this->paths = $paths;
        $this->collection_title = $collection_title;
        $this->manuscripts_url = $manuscripts_url;
    }

    public function execute() {
        $this->subtractAlphabetNumbersTable();
        $this->deleteDatabaseEntries();
        $this->deleteFiles();
        $this->deleteWikiPageIfNeeded();
        return;
    }

    private function subtractAlphabetNumbersTable() {
        $partial_url = $this->paths->getPartialUrl();
        $collection_title = $this->collection_title;
        $main_title_lowercase = $this->wrapper->getManuscriptsLowercaseTitle($partial_url);
        $alphabetnumbes_context = $this->wrapper->determineAlphabetNumbersContextFromCollectionTitle($collection_title);
        $this->wrapper->subtractAlphabetNumbers($main_title_lowercase, $alphabetnumbes_context);
        return;
    }
    
        private function deleteDatabaseEntries() {
        $partial_url = $this->paths->getPartialUrl();
        $collection_title = $this->collection_title;
        $status = $this->wrapper->deleteFromManuscripts($partial_url);

        if ($collection_title !== 'none') {
            $this->wrapper->checkAndDeleteCollectionifNeeded($this->collection_title);
        }

        return;
    }

    private function deleteFiles() {
        $paths = $this->paths;
        if ($paths->initialUploadFullPathIsConstructableFromScan()) {
            $this->deleteInitialUploadFullPath();
        }

        return $this->deleteSlicerExportFiles();
    }

    private function deleteInitialUploadFullPath() {
        $paths = $this->paths;
        $initial_upload_full_path = $paths->getInitialUploadFullPath();

        if (!$paths->isAllowedImage($initial_upload_full_path)) {
            return;
        }

        $initial_upload_base_path = $paths->getInitialUploadBasePath();
        return $this->recursiveDeleteFromPath($initial_upload_base_path);
    }

    /**
     * Delete all exported files in case something went wrong 
     */
    private function deleteSlicerExportFiles() {
        $this->deleteSliceDirectory();
        $this->deleteFullExportPathFiles();
        return;
    }

    private function deleteSliceDirectory() {
        $paths = $this->paths;
        $slice_directory = $paths->getUserExportPath() . '/' . 'slice';

        //check if the temporary directory 'slice' exists. If it does, it should be deleted. 
        if (file_exists($slice_directory)) {
            $this->recursiveDeleteFromPath($slice_directory);
        }

        return;
    }

    private function deleteFullExportPathFiles() {
        $paths = $this->paths;
        $full_export_path = $paths->getFullExportPath();
        $tile_group_url = $full_export_path . '/' . 'TileGroup0';
        $image_properties_url = $full_export_path . '/' . 'ImageProperties.xml';

        if (!is_dir($tile_group_url) || !is_file($image_properties_url)) {
            return;
        }

        return $this->recursiveDeleteFromPath($full_export_path);
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

    private function deleteWikiPageIfNeeded() {

        if (isset($this->manuscripts_url)) {
            $page_id = $this->wrapper->getPageId($this->manuscripts_url);
            $this->wrapper->deletePageFromId($page_id);
        }

        return;
    }

}
