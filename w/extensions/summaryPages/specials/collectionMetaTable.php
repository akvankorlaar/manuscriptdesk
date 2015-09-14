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

class collectionMetaTable{
  
/**
 * This class contains the HTML for the metatable
 */
 
  //class constructor
  public function __construct(){
    }
      
  /**
   * This function renders the metadata table 
   */
  public function renderTable($meta_data){
    
    $metatitle = $meta_data['collections_metatitle'];
    $metaauthor = $meta_data['collections_metaauthor'];
    $metayear = $meta_data['collections_metayear'];
    $metapages = $meta_data['collections_metapages'];
    $metacategory = $meta_data['collections_metacategory'];
    $metaproduced = $meta_data['collections_metaproduced'];    
    $metaproducer = $meta_data['collections_metaproducer'];
    $metaid = $meta_data['collections_metaid'];
    $metaeditors = $meta_data['collections_metaeditors'];
    $metajournal = $meta_data['collections_metajournal'];
    $metajournalnumber = $meta_data['collections_metajournalnumber'];
    $metatranslators = $meta_data['collections_metatranslators'];
    $metawebsource = $meta_data['collections_metawebsource'];
    $metanotes = $meta_data['collections_metanotes'];
    
     $html_table = " 
    <table id='metatable' align='center'>
      <tr>
          <th style ='text-align: center;' colspan='4'>
              Collection Title: $metatitle
          </th>
      </tr>
       <tr>
          <th style='width:25%;'>
          Author Name:
          </th>
          <td style = 'width:25%;'>
          $metaauthor
          </td>
          <th style ='width:25%;'>
          Published in Year:
          </th>
          <td style ='width:25%;'>
          $metayear
          </td>
      </tr>
       <tr>
          <th>
          Number of Pages:
          </th>
          <td>
          $metapages
          </td>
          <th>
          Category:
          </th>
          <td>
          $metacategory
          </td>
      </tr>
       <tr>
          <th>
          Produced in Year:
          </th>
          <td>
          $metaproduced
          </td>
          <th>
          Producer:
          </th>
          <td>
          $metaproducer
          </td>
      </tr>
       <tr>
          <th>
          ID Number:
          </th>
          <td>
          $metaid
          </td>
          <th>
          Editors:
          </th>
          <td>
          $metaeditors
          </td>
      </tr>
        <tr>
          <th>
          Journal:
          </th>
          <td>
          $metajournal
          </td>
          <th>
          Journal Number:
          </th>
          <td>
          $metajournalnumber
          </td>
      </tr>
           <tr>
          <th>
          Translators:
          </th>
          <td>
          $metatranslators
          </td>
          <th>
          (Web)source:
          </th>
          <td>
          $metawebsource
          </td>
      </tr>
       </tr>
           <tr>
          <th>
          Notes:
          </th>
          <td>
          $metanotes
          </td>
      </tr>
       <tr>
          <td colspan='4' style='text-align: center; background-color: #def;'>         
          </td>
      </tr>
    </table>
  ";
     
   return $html_table; 
  }
}