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
 * This file is mainly concerned with showing the loader gif, and javascript validation. There is also server side validation in SpecialNewManuscript,
 * so the javascript validation is just to increase user-experience and reduce some irrelevant requests sent to the server
 */

(function (mw, $){
     
    /**
     * This function checks if there is an input in #enter_title, and in #wpUploadFile. It also disables/enables the submit button
     * 
     * @returns {undefined}
     */
    function checkInput(){
          
      var text_input1 = $('#enter_title').val();      
      var file_input = $('#wpUploadFile').val();      
       
       if(text_input1 != '' && file_input != '' ){         
         $(".mw-htmlform-submit").removeAttr("disabled");
         $(".mw-htmlform-submit").css("color", "black");
         $(".mw-htmlform-submit").css("cursor", "pointer");
         
       }else{
         $(".mw-htmlform-submit").attr("disabled","disabled");
         $(".mw-htmlform-submit").css("color", "grey");
         $(".mw-htmlform-submit").css("cursor", "default");
      }         
    }
      
  /**
   * This function prevents users from putting non-alphanumeric charachters into the textfield
   * 
   * First this was used: if (event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 39 || regeX.test(key))
   * 
   * keyCode 8, 37 and 39 are supposed to correspond to backspace, left arrow and right arrow respectively. However, these commands were
   * possible anyway, while signs like '%' were being let through. The current code needs to be tested on other computers to see if you can still do
   * a backspace, left arrow, right arrow. 
   * 
   * @param {type} event
   * @returns {Boolean}
   */  
    function filterCharachters(event) {
    
      var regeX = /[a-zA-Z0-9\b]/g; 
      var key = String.fromCharCode(event.which);
        
      if (regeX.test(key)) {
        return true;
      }

      return false;
    };
    
    /**
     * This function disables the paste function 
     */
    function preventPaste(event){
      event.preventDefault();
    };
    
   /**
    * This function shows #begincollate-loaderdiv and hides #begincollate-form after clicking submit
    */
    $('#mw-upload-form').submit(function() {
      $('#mw-upload-form').hide();  
      $('h2').hide(); 
      $('.error').hide();
      $('#newmanuscript-loaderdiv').show();   
    });
    
    /**
     * This function disables the submit button when the document is ready 
     */
    $(document).ready(function(){
      $(".mw-htmlform-submit").attr("disabled","disabled");
    });
    
    //call the function checkInput 
    $('#enter_title').keyup(checkInput); 
    $('#wpUploadFile').change(checkInput);
    
    //call the function filterCharachters
    $( "#enter_title" ).keypress(filterCharachters);
    $('#mw-input-wpcollection_field').keypress(filterCharachters);
    
    //call the function preventPaste
    $('#enter_title').on('paste', preventPaste);
    $('#mw-input-wpcollection_field').on('paste', preventPaste);
                
}(mediaWiki, jQuery));

