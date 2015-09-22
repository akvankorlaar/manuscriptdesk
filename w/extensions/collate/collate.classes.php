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

class collate {
  
/**
 * Class collate
 */
     
  //class constructor 
  public function __construct(){
  }
  
  /**
   * Generate table
   */
  public function renderTable($titles_array, $collatex_output, $user_name = null, $date = null) {
    
    $html = "";
    
    if($user_name && $date){
      $html .= "This page has been created by: " . htmlspecialchars($user_name) . "<br> Date: " . htmlspecialchars($date) . "<br> ";
    }
    
    $collatex_output = preg_replace('/[<>]/', '', $collatex_output);
    
    //import the javascript and the css
    $html .= "
       <script> var at = $collatex_output;</script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/yui-min.js'></script>
       <script src='/w/extensions/collate/specials/javascriptcss/jquery.min.js'></script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/collatex.js'></script>
       <script type='text/javascript' src='/w/extensions/collate/specials/javascriptcss/collatexTwo.js'></script>
       <link rel='stylesheet' type='text/css' href='/w/extensions/collate/specials/javascriptcss/collatex.css'>";
     
    $html .="
      <table class='alignment'>"; 
   
    foreach($titles_array as $key=>$title){
      $html .=
      "<tr><th>" . htmlspecialchars($title) . "</th></tr>";
    }
    
    $html .= "         
    </table>
    <div id='body'>
      <div id='result'>
      </div>
    </div>"; 
      
    return $html;
  }
}