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

class prepareSlicer {
  
/**
 * Class prepareSlicer. Prepares all paths needed by the slicer, and sends the right information to slicer.pl.
 */

  public $file_name;
  
  private $import_path; //extensions/newManuscript/specials/inintialUpload/user_name/file_name" 
  private $export_path;
  private $slicer_path; 
  private $perl_path; 
  private $user_export_path;
  private $full_export_path; 
  private $extension; 
  
  /**
   * class constructor
   * 
   * @param type $file_name the file name entered by the user
   * @param type $import_path the path of the original uploaded image
   */
  public function __construct($file_name, $import_path, $extension){
   
    global $wgNewManuscriptOptions,$wgWebsiteRoot; 
       
    $this->file_name = $file_name;
    $this->import_path = $import_path;
    
    $document_root = $wgWebsiteRoot; 
    $this->export_path = $document_root . DIRECTORY_SEPARATOR . $wgNewManuscriptOptions['zoomimages_root_dir'];        
    $this->slicer_path = $document_root . $wgNewManuscriptOptions['slicer_path'];
    $this->perl_path = $wgNewManuscriptOptions['perl_path']; 
    $this->extension = $extension; 
  }
  
  /**
   * This function first checks if the paths are valid, and then processes the request
   *  
   * @return type $status is a notification whether slicing and saving the new file was succesfull. 
   */
  public function execute(){
    
    $status_paths = $this->checkPaths();
    
    if($status_paths !== true){
      return $status_paths;       
    }  
    
    $status_slicer = $this->process($this->import_path); 
    
    return $status_slicer;  
  }
  
  /**
   * Checks if the import path, the export path and the slicer path exist, and checks if a file with the same name has already been processed.
   * Creates a new export directory for the user if none exists.  
   * 
   * @global type $wgUser
   */
  private function checkPaths(){
    
    global $wgUser; 

    $import_path = $this->import_path;
    $export_path  = $this->export_path;
    $slicer_path = $this->slicer_path; 
    $file_name = $this->file_name; 
    
    $user_name = $wgUser->getName();    
    $user_export_path = $export_path . DIRECTORY_SEPARATOR .  $user_name; 
    
    if(!file_exists($user_export_path)){
      mkdir($user_export_path, 0755, true);
    }
    
    $full_export_path = $user_export_path . DIRECTORY_SEPARATOR . $file_name;
        
    if(!file_exists($import_path)){
      return 'slicer-error-importpath';
    }
    
    if(!file_exists($user_export_path)){
      return 'slicer-error-exportpath';
    }
    
    if(file_exists($full_export_path)){
      return 'slicer-error-upload';
    }
    
    if(!file_exists($slicer_path)){
      return 'slicer-error-slicerpath';     
    }
    
    $this->user_export_path = $user_export_path;
    $this->full_export_path = $full_export_path; 
    
    return true;
  }
  
  /**
   * Perform the actual slice operation, by executing the perl code (slice.pl and slicer.pl)
   * 
   * @param string $sInputImagePath
   * @return void
   */
  private function process($full_import_path){

    $slicer_path = $this->slicer_path;
    $user_export_path = $this->user_export_path;
    $perl_path = $this->perl_path; 
    $extension = $this->extension; 

    $shell_command = $perl_path . ' ' . $slicer_path . ' --input_file ' . $full_import_path . ' --output_path ' . $user_export_path . ' --extension ' . $extension;
    $shell_command = str_replace('\\', '/', $shell_command ); //is this needed? 
    $shell_command = escapeshellcmd($shell_command);

    $perl_output = '';

    ob_start();
    system($shell_command);
    $perl_output = ob_get_contents();
    ob_end_clean();

    $perl_output = str_replace( "        1 file(s) moved.\r\n",'',$perl_output);

    if(strpos(strtolower($perl_output), 'error' ) !== false || !file_exists($this->full_export_path)){
      return 'slicer-error-execute ' . $perl_output;   
    }
    
    return true;
  }
  
  /**
   * Delete all exported files in case something went wrong 
   */
  public function deleteExportFiles(){
        
    $zoom_images_file = $this->full_export_path; 
    $slice_directory = $this->user_export_path . DIRECTORY_SEPARATOR . 'slice';
    
    //check if the temporary directory 'slice' exists. If it does, it should be deleted. 
    if(file_exists($slice_directory)){
      $this->deleteAllFiles($slice_directory); 
    }
     
    $tile_group_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'TileGroup0';
    $image_properties_url = $zoom_images_file . DIRECTORY_SEPARATOR . 'ImageProperties.xml';    
    
    if(!is_dir($tile_group_url) || !is_file($image_properties_url)){
      return false; 
    }
        
    return $this->deleteAllFiles($zoom_images_file);
  }
    
  /**
   * The function recursively deleted all directories and files contained in $zoom_images_file
   * 
   * @param type $zoom_images_file
   * @return boolean
   */
  private function deleteAllFiles($zoom_images_file){
           
    //start deleting files
    if (is_dir($zoom_images_file) === true){      
      $files = array_diff(scandir($zoom_images_file), array('.', '..'));

      foreach ($files as $file){
        //recursive call
        $this->deleteAllFiles(realpath($zoom_images_file) . DIRECTORY_SEPARATOR . $file);
      }

      return rmdir($zoom_images_file);

    }elseif (is_file($zoom_images_file) === true){
      return unlink($zoom_images_file);
    }
    
    return false;
  }  
}