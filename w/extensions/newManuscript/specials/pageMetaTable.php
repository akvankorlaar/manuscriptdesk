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

class pageMetaTable{
  
/**
 * This class contains the HTML for the metatable, and extracts the text written within the tags
 */
  
  private $values;
  
  //class constructor
  public function __construct(){
    
    $this->values = null; //default value
  }
  
  /**
   * This function renders the metadata table 
   */
  public function renderTable(){
    
    $values = $this->values; 
    
    if(!isset($values)){
      return true; 
    }
    
    $html = "";
    $html .= "<table id='page-metatable' align='center'>";
    $html .= "<tr>";
    
    $a = 0;
    foreach ($values as $index=>$value){
      
      if($a % 2 === 0){        
        $html .= "</tr>";
        $html .= "<tr>";
      }
      
      $html .= "<th>" . $index . "</th>";
      $html .= "<td>" . $value . "</td>";        
      $a+=1;   
    }
    
    $html .= "</tr>";
    $html .= "</table>";
     
    return $html; 
  }
  
  /**
   * Extract options from a blob of text
   * 
   * @param string $input Tag contents
   */
  public function extractOptions($input){
    //Parse all possible options
    $values = array();
    $input_array = explode("\n", $input);
    
    foreach ($input_array as $line){
      
      if (strpos($line,'=') === false){  
        continue;    
      }
      
      list($name, $value) = explode('=', $line, 2);
      $value = preg_replace('/[^A-Za-z0-9 ]/', '', $value);
      $name = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
      $values[strtolower(trim($name))] = $value;     
    }
   
    $this->values = $values;
    
    return true;
  }  
}