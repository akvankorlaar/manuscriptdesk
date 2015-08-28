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

/**
 * This file is mainly concerned with showing the loader gif, and javascript validation. There is also server side validation in SpecialBeginCollate,
 * so the javascript validation is just to increase user-experience and reduce some irrelevant requests sent to the server
 * 
 * @param {type} mw
 * @param {type} $
 * @returns {undefined}
 */
(function (mw, $){
     
    /**
     * This function calculates how many pages are in the checked collections 
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
     * This function disables or enables the submit button, depending on how many checkboxes are checked, and how many pages are in the checked checkboxes
     */
    function changeSubmit(collection_pages){
      
      //the maximum number of pages a user is allowed to collate
      var max_number_pages = mw.config.get('wgmax_collation_pages');
      
      //count the number of checked checkboxes
      var normal_checked = $("input[class='begincollate-checkbox']:checked").length;
      var collection_checked = $("input[class='begincollate-checkbox-col']:checked").length;
      
      var total_checked = normal_checked+collection_checked;    
      var total_pages = normal_checked+collection_pages; 
                              
      if(total_checked >= 2 && total_pages <= max_number_pages){
        $("#begincollate-submitbutton").removeAttr("disabled");
        $("#begincollate-submitbutton").css("cursor", "pointer");        
        $("#javascript-error").empty();
        
      }else{
        $("#begincollate-submitbutton").attr("disabled","disabled");
        $("#begincollate-submitbutton").css("cursor", "default"); 
        
        if(total_checked < 2){
          $("#javascript-error").empty();
        }
        
        if ($('#javascript-error').is(':empty')){
          if(total_pages > max_number_pages){
            $("#javascript-error").append(mw.msg('collate-error-manytexts'));
          }
        }    
      }
    }
    
   /**
    * This function shows #begincollate-loaderdiv, the loader gif, and hides p elements, #begincollate-infobox, .error, and #begincollate-form after clicking submit
    */
    $('.begincollate-form').submit(function() {
      $('p').hide();    
      $('#begincollate-infobox').hide();
      $('.error').hide();
      $('.begincollate-form').hide();
      $('#begincollate-loaderdiv').show();
    });
   
   /**
    * This function shows #begincollate-loaderdiv, the loader gif, and hides .begincollate-form-two, p elements and #begincollate-tablewrapper after clicking submit
    */
    $('.begincollate-form-two').submit(function() {
      $('.begincollate-form-two').hide();
      $('p').hide();
      $('#begincollate-tablewrapper').hide();     
      $('#begincollate-loaderdiv').show();
    });
    
    //call the function calculateCollectionPages on change
    $('.begincollate-checkbox').change(calculateCollectionPages);  
    $('.begincollate-checkbox-col').change(calculateCollectionPages); 
       
}(mediaWiki, jQuery));