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

class textConverter{
  
/**
 * Text converter. Converts array text to json format, and sends it to collatex
 */
  
  private $url;  
  private $headers; 

    // Class constructor
  public function __construct(){ 

    global $wgCollationOptions;

    $this->url = $wgCollationOptions['collatex_url'];
    $this->headers = $wgCollationOptions['collatex_headers'];

  }

  /**
   * This function converts an array of strings into json format, in such a way that it can be handled
   * by collatex
   * 
   * @param type $text_array
   * @return type
   */
  public function convertJson($text_array){  

    //convert to json
    $length_text_array = count($text_array);
    $content = array();
    $alphabet = range('A','Z');
    
    //first make an array of the appropriate format
    for($i=0;$i<$length_text_array;$i++){
      $content["witnesses"][$i]["id"] = $alphabet[$i];
      $content["witnesses"][$i]["content"]=$text_array[$i];
    }

    //then encode the array to json
    $content = json_encode($content);

    return $content; 
  }

  /**
   * This function sends the texts to collatex, and retrieves the output
   * 
   * @param type $text_json
   * @return type
   */
  public function callCollatex($text_json){

   $url = $this->url;
   $headers = $this->headers;

   $curl = curl_init($url);   
   curl_setopt($curl, CURLOPT_URL,$url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($curl, CURLOPT_POST, true);
   curl_setopt($curl, CURLOPT_POSTFIELDS, $text_json);

   //execute the command, and get the output
   $result = curl_exec($curl); 
   
   curl_close($curl);

   return $result;
  }   
}