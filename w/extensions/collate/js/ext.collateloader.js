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

(function (mw, $){
     
    /**
     * 
     * @returns {undefined}
     */
//    function calculateCollectionPages(){
//      
//      var total_elements = 0; 
//      
//      $.each($("input[class='begincollate-checkbox-col']:checked"), function(){
//        
//        var object = jQuery.parseJSON($this); 
//        var number_elements = object.length;     
//        total_elements = total_elements + number_elements;         
//      });
//      
//      return changeSubmit(total_elements); 
//    }
    
    /**
     * This function disables or enables the submit button
     */
    function changeSubmit(){

      //count the number of checked checkboxes
      var normal_pages = $("input[type='checkbox']:checked").length;
      
      //var total_pages_checked = total_collection_pages+normal_pages;
      
      var total_pages_checked = normal_pages; 
      
      if(total_pages_checked >= 2 && total_pages_checked <= 6 ){
        var enable_submit = true;
      }else{
        var enable_submit = false; 
      }
            
      if(enable_submit){
        $("#begincollate-submitbutton").removeAttr("disabled");
        $("#begincollate-submitbutton").css("cursor", "pointer");
      }else{
        $("#begincollate-submitbutton").attr("disabled","disabled");
        $("#begincollate-submitbutton").css("cursor", "default");  
      }
    }
    
   /**
    * This function shows #begincollate-loaderdiv and hides p elements, #begincollate-infobox, .error, and #begincollate-form after clicking submit
    */
    $('.begincollate-form').submit(function() {
      $('p').hide();    
      $('#begincollate-infobox').hide();
      $('.error').hide();
      $('.begincollate-form').hide();
      $('#begincollate-loaderdiv').show();
    });
   
   /**
    * This function shows #begincollate-loaderdiv and hides .begincollate-form-two, p elements and #begincollate-tablewrapper after clicking submit
    */
    $('.begincollate-form-two').submit(function() {
      $('.begincollate-form-two').hide();
      $('p').hide();
      $('#begincollate-tablewrapper').hide();     
      $('#begincollate-loaderdiv').show();
    });
    
    //call the function changeSubmit on change
    $('.begincollate-checkbox').change(changeSubmit);  
    $('.begincollate-checkbox-col').change(calculateCollectionPages); 
       
}(mediaWiki, jQuery));