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
class UserPageViewer extends ManuscriptDeskBaseViewer {

    use HTMLJavascriptLoaderGif, HTMLPreviousNextPageLinks;

    /**
     * This function shows the form when editing a manuscript title
     * 
     * See https://www.mediawiki.org/wiki/HTMLForm/tutorial for information on the MediaWiki form builder
     */
    private function showEditTitle($error = '') {

        $out = $this->getOutput();
        $user_name = $this->user_name;
        $selected_collection = $this->selected_collection;
        $manuscript_old_title = $this->manuscript_old_title;
        $manuscript_url_old_title = $this->manuscript_url_old_title;
        $max_length = $this->max_length;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar('edit');
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";
        $html .= $this->addGoBackButton();
        $html .= "<h2>" . $this->msg('userpage-edittitle') . " " . $manuscript_old_title . "</h2>";
        $html .= $this->msg('userpage-edittitleinstruction');
        $html .= "<br><br>";

        if (!empty($error)) {
            $html .= "<div class='error'>" . $error . "</div>";
        }

        $html .= "</div>";

        $out->addHTML($html);

        $descriptor = array();

        $descriptor['titlefield'] = array(
          'label-message' => 'userpage-newmanuscripttitle',
          'class' => 'HTMLTextField',
          'default' => $manuscript_old_title,
          'maxlength' => $max_length,
        );

        $html_form = new HTMLForm($descriptor, $this->getContext());
        $html_form->setSubmitText($this->msg('metadata-submit'));
        $html_form->addHiddenField('edit_selectedcollection', $selected_collection);
        $html_form->addHiddenField('manuscriptoldtitle', $manuscript_old_title);
        $html_form->addHiddenField('manuscripturloldtitle', $manuscript_url_old_title);

        $html_form->setSubmitCallback(array('SpecialUserPage', 'processInput'));
        $html_form->show();
    }

}
