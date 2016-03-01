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
     * This function shows a confirmation of the edit after submission of the form, in case the user has reached the page via the link on a manuscript page
     * 
     * @return boolean
     */
    private function prepareRedirect() {

        $linkback = $this->linkback;
        $article_url = $this->article_url;
        $user_name = $this->user_name;
        $out = $this->getOutput();
        $html = "";

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar('edit');
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";

        $html .= "<p>" . $this->msg('userpage-editcomplete') . "</p>";

        $html .= "<form id='userpage-linkback' action='" . $article_url . $linkback . "' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='linkback' title='" . $this->msg('userpage-linkback1') . "' value='" . $this->msg('userpage-linkback2') . $linkback . "'>";
        $html .= "</form>";

        $html .= "</div>";

        return $out->addHTML($html);
    }

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

    /**
     * This function constructs the edit form for editing metadata.
     * 
     * See https://www.mediawiki.org/wiki/HTMLForm/tutorial for information on the MediaWiki form builder
     */
    private function showEditMetadata($meta_data = array(), $error = '') {

        foreach ($meta_data as $index => &$variable) {
            $variable = htmlspecialchars($variable);
        }

        $metatitle = isset($meta_data['collections_metatitle']) ? $meta_data['collections_metatitle'] : '';
        $metaauthor = isset($meta_data['collections_metaauthor']) ? $meta_data['collections_metaauthor'] : '';
        $metayear = isset($meta_data['collections_metayear']) ? $meta_data['collections_metayear'] : '';
        $metapages = isset($meta_data['collections_metapages']) ? $meta_data['collections_metapages'] : '';
        $metacategory = isset($meta_data['collections_metacategory']) ? $meta_data['collections_metacategory'] : '';
        $metaproduced = isset($meta_data['collections_metaproduced']) ? $meta_data['collections_metaproduced'] : '';
        $metaproducer = isset($meta_data['collections_metaproducer']) ? $meta_data['collections_metaproducer'] : '';
        $metaeditors = isset($meta_data['collections_metaeditors']) ? $meta_data['collections_metaeditors'] : '';
        $metajournal = isset($meta_data['collections_metajournal']) ? $meta_data['collections_metajournal'] : '';
        $metajournalnumber = isset($meta_data['collections_metajournalnumber']) ? $meta_data['collections_metajournalnumber'] : '';
        $metatranslators = isset($meta_data['collections_metatranslators']) ? $meta_data['collections_metatranslators'] : '';
        $metawebsource = isset($meta_data['collections_metawebsource']) ? $meta_data['collections_metawebsource'] : '';
        $metaid = isset($meta_data['collections_metaid']) ? $meta_data['collections_metaid'] : '';
        $metanotes = isset($meta_data['collections_metanotes']) ? $meta_data['collections_metanotes'] : '';

        $out = $this->getOutput();
        $user_name = $this->user_name;
        $selected_collection = $this->selected_collection;
        $max_length = $this->max_length;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar('edit');
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";
        $html .= $this->addGoBackButton();
        $html .= "<h2>" . $this->msg('userpage-editmetadata') . " " . $selected_collection . "</h2>";
        $html .= $this->msg('userpage-optional');
        $html .= "<br><br>";

        if (!empty($error)) {
            $html .= "<div class='error'>" . $error . "</div>";
        }

        $html .= "</div>";

        $out->addHTML($html);

        $descriptor = array();

        $descriptor['textfield1'] = array(
          'label-message' => 'metadata-title',
          'class' => 'HTMLTextField',
          'default' => $metatitle,
          'maxlength' => $max_length,
        );

        $descriptor['textfield2'] = array(
          'label-message' => 'metadata-name',
          'class' => 'HTMLTextField',
          'default' => $metaauthor,
          'maxlength' => $max_length,
        );

        $descriptor['textfield3'] = array(
          'label-message' => 'metadata-year',
          'class' => 'HTMLTextField',
          'default' => $metayear,
          'maxlength' => $max_length,
        );

        $descriptor['textfield4'] = array(
          'label-message' => 'metadata-pages',
          'class' => 'HTMLTextField',
          'default' => $metapages,
          'maxlength' => $max_length,
        );

        $descriptor['textfield5'] = array(
          'label-message' => 'metadata-category',
          'class' => 'HTMLTextField',
          'default' => $metacategory,
          'maxlength' => $max_length,
        );

        $descriptor['textfield6'] = array(
          'label-message' => 'metadata-produced',
          'class' => 'HTMLTextField',
          'default' => $metaproduced,
          'maxlength' => $max_length,
        );

        $descriptor['textfield7'] = array(
          'label-message' => 'metadata-producer',
          'class' => 'HTMLTextField',
          'default' => $metaproducer,
          'maxlength' => $max_length,
        );

        $descriptor['textfield8'] = array(
          'label-message' => 'metadata-editors',
          'class' => 'HTMLTextField',
          'default' => $metaeditors,
          'maxlength' => $max_length,
        );

        $descriptor['textfield9'] = array(
          'label-message' => 'metadata-journal',
          'class' => 'HTMLTextField',
          'default' => $metajournal,
          'maxlength' => $max_length,
        );

        $descriptor['textfield10'] = array(
          'label-message' => 'metadata-journalnumber',
          'class' => 'HTMLTextField',
          'default' => $metajournalnumber,
          'maxlength' => $max_length,
        );

        $descriptor['textfield11'] = array(
          'label-message' => 'metadata-translators',
          'class' => 'HTMLTextField',
          'default' => $metatranslators,
          'maxlength' => $max_length,
        );

        $descriptor['textfield12'] = array(
          'label-message' => 'metadata-websource',
          'class' => 'HTMLTextField',
          'default' => $metawebsource,
          'maxlength' => $max_length,
        );

        $descriptor['textfield13'] = array(
          'label-message' => 'metadata-id',
          'class' => 'HTMLTextField',
          'default' => $metaid,
          'maxlength' => $max_length,
        );

        $descriptor['textfield14'] = array(
          'type' => 'textarea',
          'labelmessage' => 'metadata-notes',
          'default' => $metanotes,
          'rows' => 20,
          'cols' => 20,
          'maxlength' => ($max_length * 10),
        );

        if (isset($this->linkback)) {

            $descriptor['hidden'] = array(
              'type' => 'hidden',
              'name' => 'linkback',
              'default' => $this->linkback,
            );
        }

        $html_form = new HTMLForm($descriptor, $this->getContext());
        $html_form->setSubmitText($this->msg('metadata-submit'));
        $html_form->addHiddenField('edit_selectedcollection', $selected_collection);
        $html_form->setSubmitCallback(array('SpecialUserPage', 'processInput'));
        $html_form->show();
    }

    /**
     * This function displays a single collection (metadata and information on the pages) to the user
     * 
     * @param type $pages_within_collection
     * @return type
     */
    private function showSingleCollection($single_collection_data) {

        $out = $this->getOutput();
        $user_name = $this->user_name;
        $article_url = $this->article_url;
        $selected_collection = $this->selected_collection;
        list($meta_data, $pages_within_collection) = $single_collection_data;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar();
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";

        $html .= "<form id='userpage-editmetadata' action='" . $article_url . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='editmetadata' value='" . $this->msg('userpage-editmetadatabutton') . "'>";
        $html .= "<input type='hidden' name='selectedcollection' value='" . $selected_collection . "'>";
        $html .= "</form>";

        //redirect to Special:NewManuscript, and automatically have the current collection selected
        $html .= "<form id='userpage-addnewpage' action='" . $article_url . "Special:NewManuscript' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='addnewpage' title='" . $this->msg('userpage-newcollection') . "' value='Add New Page'>";
        $html .= "<input type='hidden' name='selected_collection' value='" . $selected_collection . "'>";
        $html .= "</form>";

        $html .= "<h2 style='text-align: center;'>" . $this->msg('userpage-collection') . ": " . $selected_collection . "</h2>";
        $html .= "<br>";
        $html .= "<h3>" . $this->msg('userpage-metadata') . "</h3>";

        $collection_meta_table = new HTMLcollectionMetaTable();

        $html .= $collection_meta_table->getHTMLCollectionMetaTable($meta_data);

        $html .= "<h3>Pages</h3>";
        $html .= $this->msg('userpage-contains') . " " . count($pages_within_collection) . " " . $this->msg('userpage-contains2');
        $html .= "<br>";

        $html .= "<form id='userpage-edittitle' action='" . $article_url . "Special:UserPage' method='post'>";
        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-three'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td class='td-three'><b>" . $this->msg('userpage-creationdate') . "</b></td>";
        $html .= "<td class='td-three'></td>";
        $html .= "</tr>";

        $counter = 0;

        foreach ($pages_within_collection as $key => $array) {

            $manuscripts_url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
            $manuscripts_title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
            $manuscripts_date = isset($array['manuscripts_date']) ? $array['manuscripts_date'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-three'><a href='" . $article_url . htmlspecialchars($manuscripts_url) . "' title='" . htmlspecialchars($manuscripts_url) . "'>"
                . htmlspecialchars($manuscripts_title) . "</a></td>";
            $html .= "<td class='td-three'>" . htmlspecialchars($manuscripts_date) . "</td>";
            $html .= "<td class='td-three'><input type='submit' class='button-transparent' name='changetitle_button" . $counter . "' "
                . "value='" . $this->msg('userpage-changetitle') . "'></td>";
            $html .= "<input type='hidden' name='oldtitle" . $counter . "' value = '" . htmlspecialchars($manuscripts_title) . "'>";
            $html .= "<input type='hidden' name='urloldtitle" . $counter . "' value = '" . htmlspecialchars($manuscripts_url) . "'>";
            $html .= "</tr>";

            $counter+=1;
        }

        $html .= "<input type='hidden' name='edit_selectedcollection' value = '" . $selected_collection . "'>";

        $html .= "</table>";
        $html .= "</form>";
        $html .= "</div>";

        return $out->addHTML($html);
    }

    /**
     * This function constructs html for a go back button
     */
    private function addGoBackButton() {

        $article_url = $this->article_url;
        $selected_collection = $this->selected_collection;

        $html = "";
        $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $article_url . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' value='" . $this->msg('userpage-goback') . "'>";
        $html .= "<input type='hidden' name='singlecollection' value='" . htmlspecialchars($selected_collection) . "'>";
        $html .= "</form>";

        return $html;
    }


    public function getPageName(){
        
    }

}
