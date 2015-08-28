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
    function checkInput(){
          
      var text_input1 = $('#enter_title').val();
      var text_input2 = $('#mw-input-wpcollection_field').val();
      
      var file_input = $('#wpUploadFile').val();      
       
       if(text_input1 != '' && text_input2 != '' && file_input != '' && text_input1.match(/[a-zA-Z0-9]/g) && text_input2.match(/[a-zA-Z0-9]/g)){         
       //match does not work....... 
         $(".mw-htmlform-submit").removeAttr("disabled");
         $(".mw-htmlform-submit").css("cursor", "pointer");        
       }
    }
     
   /**
    * This function shows #begincollate-loaderdiv and hides #begincollate-form after clicking submit
    */
    $('#mw-upload-form').submit(function() {
      $('#mw-upload-form').hide();  
      $('h2').hide(); 
      $('.error').hide();
      $('#newmanuscript-loaderdiv').show();   
    });
    
    //call the function checkValidation on change
    $('#enter_title').keyup(checkInput);  
    $('#mw-input-wpcollection_field').keyup(checkInput); 
    $('#wpUploadFile').change(checkInput);
              
}(mediaWiki, jQuery));

