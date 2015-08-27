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
    function calculateCollectionPages(){
      
      var collection_pages = 0; 
      
      $.each($("input[class='begincollate-checkbox-col']:checked"), function(){
        
        var current_value = $(this).val();
        var object = jQuery.parseJSON(current_value); 
        var number_elements = object.length;     
        collection_pages = collection_pages + number_elements;         
      });
      
      return changeSubmit(collection_pages); 
    }
    
    /**
     * This function disables or enables the submit button
     */
    function changeSubmit(collection_pages){

      //count the number of checked checkboxes
      var normal_checked = $("input[class='begincollate-checkbox']:checked").length;
      var collection_checked = $("input[class='begincollate-checkbox-col']:checked").length;
      
      var total_checked = normal_checked+collection_checked;
      
      var total_pages = normal_checked+collection_pages; 
                  
      //this should not be true if only 1 collection is checked
      if(total_checked >= 2 && total_pages <= 5){
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
    $('.begincollate-checkbox').change(calculateCollectionPages);  
    $('.begincollate-checkbox-col').change(calculateCollectionPages); 
       
}(mediaWiki, jQuery));