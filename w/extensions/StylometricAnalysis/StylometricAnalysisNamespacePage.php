<?php

/**
 * This file is part of the collate extension
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

class StylometricAnalysisNamespacePage {
  
  public function __construct(){
      
  }
  
  public function renderPage(array $data) {
    
    $html = "";
    
    $user_name = isset($data['user']) ? $data['user'] : '';
    $time = isset($data['time']) ? $data['time'] : '';
    $full_outputpath1 = isset($data['full_outputpath1']) ? $data['full_outputpath1'] : '';
    $full_outputpath2 = isset($data['full_outputpath2']) ? $data['full_outputpath2'] : '';
    $config_array = isset($data['config_array']) ? $data['config_array'] : '';
    $date = isset($data['date']) ? $data['date'] : ''; 
    
    if(!empty($user_name) && !empty($date)){
      $html .= "This page has been created by: " . htmlspecialchars($user_name) . "<br> Date: " . htmlspecialchars($date) . "<br> ";
    }
    
    //render the html for the images .. 
    
    //show the other data variables
      
    return $html;
  }
}
