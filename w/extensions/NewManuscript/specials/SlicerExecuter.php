<?php

/**
 * This file is part of the NewManuscript extension
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
        
        if(!$this->paths->imageUploaded()){
            throw new \Exception('slicer-error-execute');
        }
        
        $shell_command = $perl_path . ' ' . $slicer_path . ' --input_file ' . $initial_upload_full_path . ' --output_path ' . $user_export_path . ' --extension ' . $extension;
        $shell_command = str_replace('\\', '/', $shell_command); //is this needed? 
        $shell_command = escapeshellcmd($shell_command);
        return $shell_command;
    }
    
}
