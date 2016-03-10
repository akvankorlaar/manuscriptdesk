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

(function (mw, $) {

  /**
   * This function disables or enables the submit button, depending on how many checkboxes are checked
   */
  function changeSubmit() {

    var min_number_checked = mw.config.get('wgmin_stylometricanalysis_collections');
    var max_number_checked = mw.config.get('wgmax_stylometricanalysis_collections');

    //count the number of checked checkboxes
    var collection_checked = $("input[class='stylometricanalysis-checkbox']:checked").length;

    //enable the submit button if at least min_number_pages are checked                            
    if (collection_checked >= min_number_checked && collection_checked <= max_number_checked) {
      $("#stylometricanalysis-submitbutton").removeAttr("disabled");
      $("#stylometricanalysis-submitbutton").css("cursor", "pointer");
      $(".javascripterror").empty();

    } else {
      $("#stylometricanalysis-submitbutton").attr("disabled", "disabled");
      $("#stylometricanalysis-submitbutton").css("cursor", "default");

      if (collection_checked < min_number_checked) {
        $(".javascripterror").empty();
      }

      if ($('.javascripterror').is(':empty')) {
        if (collection_checked > max_number_checked) {
          $(".javascripterror").append(mw.msg('stylometricanalysis-error-manycollections'));
        }
      }
    }
  }

  //call the function changeSubmit on change
  $('.stylometricanalysis-checkbox').change(changeSubmit);

  $(document).ready(function () {
    $("#stylometricanalysis-submitbutton").attr("disabled", "disabled");
  });

}(mediaWiki, jQuery));