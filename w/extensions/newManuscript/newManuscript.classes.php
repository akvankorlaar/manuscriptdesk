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

class metaTable{
  
/**
 * This class contains the HTML for the metatable, and extracts the text written within the tags
 */

  private $title_v = ""; 
  private $author_v = "";
  private $date_v = "";
  private $original_image_name_v = "";
  private $image_number_v = "";
  private $page_number_v = "";
  private $info_in_main_headings_field_v = "";
  private $marginal_summary_numbering_v = "";
  private $category_v = "";
  private $number_of_pages_v = "";
  private $recto_verso_v = "";
  private $penner_v = "";
  private $watermarks_v = "";
  private $marginals_v = "";
  private $paper_producer_v = "";
  private $corrections_v = "";
  private $produced_in_year_v = "";
  private $notes_public_v = "";
  private $id_number_v = "";
  
  //class constructor
  public function __construct(){
    }
  
  /**
   * This function renders the metadata table 
   */
  public function renderTable(){
    
     $html_table = " 
   <body>
    <table id='metatable' align='center'>
      <tr>
          <th style ='text-align: center;' colspan='4'>
              Title: $this->title_v
          </th>
      </tr>
       <tr>
          <th style='width:25%;'>
          Author:
          </th>
          <td style = 'width:25%;'>
          $this->author_v
          </td>
          <th style ='width:25%;'>
          Date:
          </th>
          <td style ='width:25%;'>
          $this->date_v
          </td>
      </tr>
       <tr>
          <th>
          Original Image Name:
          </th>
          <td>
          $this->original_image_name_v
          </td>
          <th>
          Image Number:
          </th>
          <td>
          $this->image_number_v
          </td>
      </tr>
       <tr>
          <th>
          Page Number(s):
          </th>
          <td>
          $this->page_number_v
          </td>
          <th>
          Info in Main Headings Field:
          </th>
          <td>
          $this->info_in_main_headings_field_v
          </td>
      </tr>
       <tr>
          <th>
          Marginal Summary Numbering:
          </th>
          <td>
          $this->marginal_summary_numbering_v
          </td>
          <th>
          Category:
          </th>
          <td>
          $this->category_v
          </td>
      </tr>
       <tr>
          <th>
          Number of Pages:
          </th>
          <td>
          $this->number_of_pages_v
          </td>
          <th>
          Recto Verso:
          </th>
          <td>
          $this->recto_verso_v
          </td>
      </tr>
       <tr>
          <th>
          Penner:
          </th>
          <td>
          $this->penner_v
          </td>
          <th>
          Watermarks:
          </th>
          <td>
          $this->watermarks_v
          </td>
      </tr>
       <tr>
          <th>
          Marginals:
          </th>
          <td>
          $this->marginals_v
          </td>
          <th>
          Paper Producer:
          </th>
          <td>
          $this->paper_producer_v
          </td>
      </tr>
       <tr>
          <th>
          Corrections:
          </th>
          <td>
          $this->corrections_v
          </td>
          <th>
          Produced in Year:
          </th>
          <td>
          $this->produced_in_year_v
          </td>
      </tr>
       <tr>
          <th>
          Notes Public: 
          </th>
          <td>
          $this->notes_public_v
          </td>
          <th>
          ID Number:
          </th>
          <td>
          $this->id_number_v
          </td>
      </tr>
       <tr>
          <td colspan='4' style='text-align: center; background-color: #def;'>         
          </td>
      </tr>
    </table>
  </body>
  ";
     
   return $html_table; 
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
      $value = Sanitizer::decodeCharReferences(trim($value));
      $value = strip_tags($value);
      $values[strtolower(trim($name))] = $value;     
    }
    
    // Build list of options, with local member names
    $options = array(
      'title' => 'title_v',
      'author' => 'author_v',
      'date' => 'date_v',
      'original_image_name' => 'original_image_name_v',
      'image_number' => 'image_number_v',
      'page_number' => 'page_number_v',
      'info_in_main_headings_field' => 'info_in_main_headings_field_v',
      'marginal_summary_numbering' => 'marginal_summary_numbering_v',
      'category' => 'category_v',
      'number_of_pages' => 'number_of_pages_v',
      'recto_verso' => 'recto_verso_v',
      'penner' => 'penner_v',
      'watermarks' => 'watermarks_v',
      'marginals' => 'marginals_v',
      'paper_producer' => 'paper_producer_v',
      'corrections' => 'corrections_v',
      'produced_in_year' => 'produced_in_year_v',
      'notes_public' => 'notes_public_v',
      'id_number' => 'id_number_v',
    );
    
    foreach ($options as $name => $var){
      
      if (isset($values[$name])){
        $this->$var = $values[$name];    
      }
    }  
  }  
}