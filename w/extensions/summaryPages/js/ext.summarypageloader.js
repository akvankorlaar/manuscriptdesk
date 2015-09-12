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
  
   var dots = 0;
   
   /**
    * This function shows #begincollate-loaderdiv and hides #begincollate-form after clicking submit
    */
   $('.summarypage-form').submit(function() {
     $('#summarypage-loaderdiv').show(); 
     $('p').hide();
     $('br').hide();
     $('#previous-link').hide();
     $('#next-link').hide();
     $('#userpage-table').hide();     
   });
         
  /**
   * This function sets the interval when calling the loader() function
   */       
   $(document).ready(function(){
    setInterval (loader, 600);
   });

  /**
   * This function appends dots to the message specified in #begincollate-loaderdiv
   * 
   * @returns {undefined}
   */
  function loader(){
    
   if(dots < 3){
      $('#summarypage-loaderspan').append('.');
      dots++;
   }else{
     $('#summarypage-loaderspan').html('');
     dots = 0;
   }
}
    
}(mediaWiki, jQuery));

