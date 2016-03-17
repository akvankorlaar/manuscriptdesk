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
 */
class SlicerExecuter {

    private $paths;
    
    public function __construct(NewManuscriptPaths $paths) {
        $this->paths = $paths; 
    }
 
    public function execute(){

        $shell_command = $this->constructShellCommand();
        $perl_output = '';

        ob_start();
        system($shell_command);
        $perl_output = ob_get_contents();
        ob_end_clean();

        if (strpos(strtolower($perl_output), 'error') !== false || !file_exists($this->paths->getFullExportPath())) {
            throw new \Exception('slicer-error-execute');
        }

        return;
    }

    private function constructShellCommand() {      
        $perl_path = $this->paths->getPerlPath();
        $slicer_path = $this->paths->getSlicerPath();
        $initial_upload_full_path = $this->paths->getInitialUploadFullPath();
        $user_export_path = $this->paths->getUserExportPath();
        $extension = $this->paths->getExtension();
        
        $shell_command = $perl_path . ' ' . $slicer_path . ' --input_file ' . $initial_upload_full_path . ' --output_path ' . $user_export_path . ' --extension ' . $extension;
        $shell_command = str_replace('\\', '/', $shell_command); //is this needed? 
        $shell_command = escapeshellcmd($shell_command);
        return $shell_command;
    }

    /**
     * Delete all exported files in case something went wrong 
     */
    public function deleteExportFiles() {

        $zoom_images_file = $this->full_export_path;
        $slice_directory = $this->user_export_path . DIRECTORY_SEPARATOR . 'slice';

        //check if the temporary directory 'slice' exists. If it does, it should be deleted. 
        if (file_exists($slice_directory)) {
            $this->deleteAllFiles($slice_directory);
        }

        $tile_group_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'TileGroup0';
        $image_properties_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'ImageProperties.xml';

        if (!is_dir($tile_group_url) || !is_file($image_properties_url)) {
            return false;
        }

        return $this->deleteAllFiles($zoom_images_file);
    }

    /**
     * The function recursively deleted all directories and files contained in $zoom_images_file
     */
    private function deleteAllFiles($zoom_images_file) {

        if (is_dir($zoom_images_file) === true) {
            $files = array_diff(scandir($zoom_images_file), array('.', '..'));

            foreach ($files as $file) {
                //recursive call
                $this->deleteAllFiles(realpath($zoom_images_file) . DIRECTORY_SEPARATOR . $file);
            }

            return rmdir($zoom_images_file);
        }
        elseif (is_file($zoom_images_file) === true) {
            return unlink($zoom_images_file);
        }

        return false;
    }

}
