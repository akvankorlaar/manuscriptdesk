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
   * This function calculates how many pages are in the checked collections 
   */
  function calculateNumberOfPagesInCheckedCollections() {

    var number_of_pages_in_checked_collections = 0;

    $.each($("input[class='collate-checkbox-col']:checked"), function () {
      var current_value = $(this).val();
      var object = jQuery.parseJSON(current_value);
      var number_elements = object.length;
      number_of_pages_in_checked_collections = number_of_pages_in_checked_collections + number_elements;
    });

    return changeSubmit(number_of_pages_in_checked_collections);
  }

  /**
   * This function disables or enables the submit button, depending on how many checkboxes are checked, and how many pages are in the checked checkboxes
   */
  function changeSubmit(number_of_pages_in_checked_collections) {

    //the maximum number of pages a user is allowed to collate
    var max_number_pages = mw.config.get('wgmax_collation_pages');
    var min_number_pages = mw.config.get('wgmin_collation_pages');

    //count the number of checked checkboxes
    var normal_checkboxes_checked = $("input[class='collate-checkbox']:checked").length;
    var collection_checkboxes_checked = $("input[class='collate-checkbox-col']:checked").length;

    var total_checkboxes_checked = normal_checkboxes_checked + collection_checkboxes_checked;
    var total_pages_in_checked_checkboxes = normal_checkboxes_checked + number_of_pages_in_checked_collections;

    //enable the submit button if at least min_number_pages are checked, but the pages within these checkboxes does not exceed max_number_pages                            
    if (total_checkboxes_checked >= min_number_pages && total_pages_in_checked_checkboxes <= max_number_pages) {
      $("#collate-submitbutton").removeAttr("disabled");
      $("#collate-submitbutton").css("cursor", "pointer");
      $(".javascripterror").empty();

    } else {
      $("#collate-submitbutton").attr("disabled", "disabled");
      $("#collate-submitbutton").css("cursor", "default");

      if (total_checkboxes_checked < min_number_pages) {
        $(".javascripterror").empty();
      }

      if ($('.javascripterror').is(':empty')) {
        if (total_pages_in_checked_checkboxes > max_number_pages) {
          $(".javascripterror").append(mw.msg('collate-error-manytexts'));
        }
      }
    }
  }

  //call the function calculateCollectionPages on change
  $('.collate-checkbox').change(calculateNumberOfPagesInCheckedCollections);
  $('.collate-checkbox-col').change(calculateNumberOfPagesInCheckedCollections);

  $(document).ready(function () {
    $("#collate-submitbutton").attr("disabled", "disabled");
  });

}(mediaWiki, jQuery));