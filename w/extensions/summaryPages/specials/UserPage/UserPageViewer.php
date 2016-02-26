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

    use HTMLUserPageMenuBar,
        HTMLJavascriptLoaderGif;

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
     * This function adds html used for the summarypage loader (see ext.summarypageloader)
     */
    private function addSummaryPageLoader() {

        //shows after submit has been clicked
        $html = "<h3 id='summarypage-loaderdiv' style='display: none;'>Loading";
        $html .= "<span id='summarypage-loaderspan'></span>";
        $html .= "</h3>";

        return $html;
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

    /**
     * This function shows the page after a request has been processed
     * 
     * @param type $title_array
     */
    private function showPage($title_array) {

        $out = $this->getOutput();
        $article_url = $this->article_url;
        $user_name = $this->user_name;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar();
        $html .= $this->getHTMLJavascriptLoaderGif();

        if (empty($title_array)) {

            $html .= "<div id='userpage-messagewrap'>";

            if ($this->view_manuscripts) {
                $html .= "<p>" . $this->msg('userpage-nomanuscripts') . "</p>";
                $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $this->msg('userpage-newmanuscriptpage') . "</a></p>";
            }

            if ($this->view_collations) {
                $html .= "<p>" . $this->msg('userpage-nocollations') . "</p>";
                $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:BeginCollate'>" . $this->msg('userpage-newcollation') . "</a></p>";
            }

            if ($this->view_collections) {
                $html .= "<p>" . $this->msg('userpage-nocollections') . "</p>";
                $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $this->msg('userpage-newcollection') . "</a></p>";
            }

            $html .= "</div>";

            return $out->addHTML($html);
        }

        if ($this->previous_page_possible) {

            $previous_offset = ($this->offset) - ($this->max_on_page);

            $previous_message_hover = $this->msg('singlemanuscriptpages-previoushover');
            $previous_message = $this->msg('singlemanuscriptpages-previous');

            $html .='<form class="summarypage-form" id="previous-link" action="' . $article_url . 'Special:UserPage" method="post">';
            $html .= "<input type='hidden' name='offset' value = '$previous_offset'>";
            $html .= "<input type='hidden' name='$this->button_name' value='$this->button_name'>";
            $html .= "<input type='submit' name = 'redirect_page_back' class='button-transparent' title='$previous_message_hover' value='$previous_message'>";
            $html.= "</form>";
        }

        if ($this->next_page_possible) {

            if (!$this->previous_page_possible) {
                $html.='<br>';
            }

            $next_message_hover = $this->msg('singlemanuscriptpages-nexthover');
            $next_message = $this->msg('singlemanuscriptpages-next');

            $html .='<form class="summarypage-form" id="next-link" action="' . $article_url . 'Special:UserPage" method="post">';
            $html .= "<input type='hidden' name='offset' value = '$this->next_offset'>";
            $html .="<input type='hidden' name='$this->button_name' value='$this->button_name'>";
            $html .= "<input type='submit' name = 'redirect_page_forward' class='button-transparent' title='$next_message_hover' value='$next_message'>";
            $html.= "</form>";
        }

        $created_message = $this->msg('userpage-created');
        $html .= "<br>";

        if ($this->view_manuscripts) {

            $html .= "<p>" . $this->msg('userpage-manuscriptinstr') . "</p>";
            $html .= "<table id='userpage-table' style='width: 100%;'>";
            $html .= "<tr>";
            $html .= "<td class='td-long'><b>" . $this->msg('userpage-tabletitle') . "</b></td>";
            $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
            $html .= "</tr>";

            foreach ($title_array as $key => $array) {

                $title = isset($array['manuscripts_title']) ? $array['manuscripts_title'] : '';
                $url = isset($array['manuscripts_url']) ? $array['manuscripts_url'] : '';
                $date = $array['manuscripts_date'] !== '' ? $array['manuscripts_date'] : 'unknown';

                $html .= "<tr>";
                $html .= "<td class='td-long'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" .
                    htmlspecialchars($title) . "</a></td>";
                $html .= "<td>" . htmlspecialchars($date) . "</td>";
                $html .= "</tr>";
            }

            $html .= "</table>";
        }

        if ($this->view_collations) {

            $html .= "<table id='userpage-table' style='width: 100%;'>";
            $html .= "<tr>";
            $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
            $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
            $html .= "</tr>";

            foreach ($title_array as $key => $array) {

                $url = isset($array['collations_url']) ? $array['collations_url'] : '';
                $date = isset($array['collations_date']) ? $array['collations_date'] : '';
                $title = isset($array['collations_main_title']) ? $array['collations_main_title'] : '';

                $html .= "<tr>";
                $html .= "<td class='td-long'><a href='" . $article_url . htmlspecialchars($url) . "' title='" . htmlspecialchars($title) . "'>" .
                    htmlspecialchars($title) . "</a></td>";
                $html .= "<td>" . htmlspecialchars($date) . "</td>";
                $html .= "</tr>";
            }

            $html .= "</table>";
        }

        if ($this->view_collections) {

            $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $article_url . "Special:UserPage' method='post'>";
            $html .= "<table id='userpage-table' style='width: 100%;'>";
            $html .= "<tr>";
            $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
            $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
            $html .= "</tr>";

            foreach ($title_array as $key => $array) {

                $collections_title = isset($array['collections_title']) ? $array['collections_title'] : '';
                $collections_date = isset($array['collections_date']) ? $array['collections_date'] : '';

                $html .= "<tr>";
                $html .= "<td class='td-long'><input type='submit' class='userpage-collectionlist' name='singlecollection' value='" . htmlspecialchars($collections_title) . "'></td>";
                $html .= "<td>" . htmlspecialchars($collections_date) . "</td>";
                $html .= "</tr>";
            }

            $html .= "</table>";
            $html .= "<input type='hidden' name='viewcollections' value='viewcollections'>";
            $html .= "</form>";
        }

        return $out->addHTML($html);
    }

    /**
     * This function shows the default page if no request was posted 
     */
    public function showDefaultPage($error_message = '', $user_name, $user_is_a_sysop, array $button_ids) {

        global $wgArticleUrl;

        $out = $this->out;
        $article_url = $wgArticleUrl;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($button_ids);
        $html .= $this->getHTMLJavascriptLoaderGif();

        if ($user_is_a_sysop) {
            $html .= $this->getHTMLMessageSpaceLeftOnDisk();
        }

        return $out->addHTML($html);
    }

    /**
     * This function constructs the menu bar for the user page
     */
    protected function getHTMLUserPageMenuBar(array $button_ids) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();

        $manuscripts_message = $this->msg('userpage-mymanuscripts');
        $collations_message = $this->msg('userpage-mycollations');
        $collections_message = $this->msg('userpage-mycollections');

        $id_manuscripts = isset($this->id_manuscripts) ? $this->id_manuscripts : 'button';
        $id_collations = isset($this->id_collations) ? $this->id_collations : 'button';

        $id_collections = isset($button_ids[0]) ? $button_ids[0] : 'button';
        $id_collections = isset($button_ids[1]) ? $button_ids[1] : 'button';
        $id_collections = isset($button_ids[2]) ? $button_ids[2] : 'button';

        $html = '<form class="summarypage-form" action="' . $article_url . 'Special:UserPage" method="post">';
        $html .= "<input type='submit' name='view_manuscripts_posted' id='$id_manuscripts' value='$manuscripts_message'>";
        $html .= "<input type='submit' name='view_collations_posted' id='$id_collations' value='$collations_message'>";
        $html .= "<input type='submit' name='view_collections_posted' id='$id_collections' value='$collections_message'>";
        $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= '</form>';

        return $html;
    }

    private function getHTMLMessageSpaceLeftOnDisk() {

        global $wgPrimaryDisk;
        $pimary_disk = $wgPrimaryDisk;

        $free_disk_space_bytes = disk_free_space($primary_disk);
        $free_disk_space_mb = round($free_disk_space_bytes / 1048576);
        $free_disk_space_gb = round($free_disk_space_mb / 1024);

        $admin_message1 = $this->msg('userpage-admin1');
        $admin_message2 = $this->msg('userpage-admin2');
        $admin_message3 = $this->msg('userpage-admin3');
        $admin_message4 = $this->msg('userpage-admin4');

        return "<p>" . $admin_message1 . ' ' . $free_disk_space_bytes . ' ' . $admin_message2 . ' ' . $free_disk_space_mb . ' ' . $admin_message3 . ' ' . $free_disk_space_gb . ' ' . $admin_message4 . ".</p>";
    }

}
