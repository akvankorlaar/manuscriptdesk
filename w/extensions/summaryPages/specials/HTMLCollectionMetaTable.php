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

trait HTMLCollectionMetaTable{
      
  /**
   * This function renders the metadata table 
   */
  protected function getHTMLCollectionMetaTable(array $meta_data){
    
    //get the data  
    $metatitle =         isset($meta_data['collections_metatitle']) ? $meta_data['collections_metatitle'] : '';
    $metaauthor =        isset($meta_data['collections_metaauthor']) ? $meta_data['collections_metaauthor'] : '';
    $metayear =          isset($meta_data['collections_metayear']) ? $meta_data['collections_metayear'] :'';
    $metapages =         isset($meta_data['collections_metapages']) ? $meta_data['collections_metapages'] : '';
    $metacategory =      isset($meta_data['collections_metacategory']) ? $meta_data['collections_metacategory'] : '';
    $metaproduced =      isset($meta_data['collections_metaproduced']) ? $meta_data['collections_metaproduced'] : '';
    $metaproducer =      isset($meta_data['collections_metaproducer']) ? $meta_data['collections_metaproducer'] : '';
    $metaeditors =       isset($meta_data['collections_metaeditors']) ? $meta_data['collections_metaeditors'] : '';
    $metajournal =       isset($meta_data['collections_metajournal']) ? $meta_data['collections_metajournal'] : '';
    $metajournalnumber = isset($meta_data['collections_metajournalnumber']) ? $meta_data['collections_metajournalnumber'] : '';
    $metatranslators =   isset($meta_data['collections_metatranslators']) ? $meta_data['collections_metatranslators'] : '';
    $metawebsource =     isset($meta_data['collections_metawebsource']) ? $meta_data['collections_metawebsource'] : '';
    $metaid =            isset($meta_data['collections_metaid']) ? $meta_data['collections_metaid'] : '';
    $metanotes =         isset($meta_data['collections_metanotes']) ? $meta_data['collections_metanotes'] : '';
    
    //get the messages
    $metadata_title = $this->getMessage('metadata-title');
    $metadata_name = $this->getMessage('metadata-name');
    $metadata_year = $this->getMessage('metadata-year');
    $metadata_pages = $this->getMessage('metadata-pages');
    $metadata_category = $this->getMessage('metadata-category');
    $metadata_produced = $this->getMessage('metadata-produced');
    $metadata_producer = $this->getMessage('metadata-producer');
    $metadata_editors = $this->getMessage('metadata-editors');
    $metadata_journal = $this->getMessage('metadata-journal');
    $metadata_journalnumber = $this->getMessage('metadata-journalnumber');
    $metadata_translators = $this->getMessage('metadata-translators');
    $metadata_websource = $this->getMessage('metadata-websource');
    $metadata_id = $this->getMessage('metadata-id');
       
    //construct the table
     $html_table = " 
    <table id='metatable' align='center'>
      <tr>
          <th style ='text-align: center;' colspan='4'>
              $metadata_title: $metatitle
          </th>
      </tr>
       <tr>
          <th>
          $metadata_name:
          </th>
          <td>
          $metaauthor
          </td>
          <th>
          $metadata_year:
          </th>
          <td>
          $metayear
          </td>
      </tr>
       <tr>
          <th>
          $metadata_pages:
          </th>
          <td>
          $metapages
          </td>
          <th>
          $metadata_category:
          </th>
          <td>
          $metacategory
          </td>
      </tr>
       <tr>
          <th>
          $metadata_produced:
          </th>
          <td>
          $metaproduced
          </td>
          <th>
          $metadata_producer:
          </th>
          <td>
          $metaproducer
          </td>
      </tr>
       <tr>
          <th>
          $metadata_id:
          </th>
          <td>
          $metaid
          </td>
          <th>
          $metadata_editors:
          </th>
          <td>
          $metaeditors
          </td>
      </tr>
        <tr>
          <th>
          $metadata_journal:
          </th>
          <td>
          $metajournal
          </td>
          <th>
          $metadata_journalnumber:
          </th>
          <td>
          $metajournalnumber
          </td>
      </tr>
           <tr>
          <th>
          $metadata_translators:
          </th>
          <td>
          $metatranslators
          </td>
          <th>
          $metadata_websource:
          </th>
          <td>
          $metawebsource
          </td>
      </tr>
       </tr>
        <tr>
          <th colspan='4' style='text-align: center; background-color: #f7f7f7;'>
          $metanotes
          </th>
       </tr>
    </table>
  ";
     
   return $html_table; 
  }
  
}