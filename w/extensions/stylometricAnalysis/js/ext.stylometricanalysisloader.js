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
     * This function disables or enables the submit button, depending on how many checkboxes are checked
     */
    function changeSubmit(){
            
      var min_number_pages = mw.config.get('wgmin_stylometricanalysis_pages');
      
      //count the number of checked checkboxes
      var collection_checked = $("input[class='stylometricanalysis-checkbox']:checked").length;
                                    
      //enable the submit button if at least min_number_pages are checked                            
      if(collection_checked >= min_number_pages){
        $("#stylometricanalysis-submitbutton").removeAttr("disabled");
        $("#stylometricanalysis-submitbutton").css("cursor", "pointer");        
        
      }else{
        $("#stylometricanalysis-submitbutton").attr("disabled","disabled");
        $("#stylometricanalysis-submitbutton").css("cursor", "default");                
      }
    }
    
   /**
    * This function shows #stylometricanalysis-loaderdiv, the loader gif, and hides p elements, #stylometricanalysis-infobox, .error, and #stylometricanalysis-form after clicking submit
    */
    $('#stylometricanalysis-form').submit(function() {
      $('#stylometricanalysis-infobox').hide();
      $('p').hide();    
      $('.error').hide();
      $('#stylometricanalysis-form').hide();
      $('#stylometricanalysis-loaderdiv').show();
    });
   
   /**
    * This function shows #stylometricanalysis-loaderdiv, the loader gif, and hides .stylometricanalysis-form-two, p elements after clicking submit
    */
    $('#stylometricanalysis-form-two').submit(function() {
      $('#stylometricanalysis-form-two').hide();
      $('p').hide();
      $('#stylometricanalysis-loaderdiv').show();
    });
    
    //call the function changeSubmit on change
    $('.stylometricanalysis-checkbox').change(changeSubmit);  
       
}(mediaWiki, jQuery));