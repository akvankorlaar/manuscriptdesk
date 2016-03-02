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
class UserPageCollectionsViewer extends ManuscriptDeskBaseViewer {

    use HTMLUserPageMenuBar,
        HTMLJavascriptLoaderGif,
        HTMLPreviousNextPageLinks, HTMLCollectionMetaTable;

    private $out;
    private $user_name;

    public function __construct(OutputPage $out, $user_name) {
        $this->out = $out;
        $this->user_name = $user_name;
    }

    public function showPage($button_name, $page_titles, $offset, $next_offset) {

        $out = $this->out;
        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $user_name = $this->user_name;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderGif();
        $html .= $this->getHTMLPreviousNextPageLinks($out, $button_name, $offset, $next_offset, 'UserPage');

        $created_message = $this->msg('userpage-created');
        $html .= "<br>";

        $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $article_url . "Special:UserPage' method='post'>";
        $html .= "<table id='userpage-table' style='width: 100%;'>";
        $html .= "<tr>";
        $html .= "<td class='td-long'>" . "<b>" . $this->msg('userpage-tabletitle') . "</b>" . "</td>";
        $html .= "<td><b>" . $this->msg('userpage-creationdate') . "</b></td>";
        $html .= "</tr>";

        foreach ($page_titles as $key => $array) {

            $collections_title = isset($array['collections_title']) ? $array['collections_title'] : '';
            $collections_date = isset($array['collections_date']) ? $array['collections_date'] : '';

            $html .= "<tr>";
            $html .= "<td class='td-long'><input type='submit' class='userpage-collectionlist' name='single_collection' value='" . htmlspecialchars($collections_title) . "'></td>";
            $html .= "<td>" . htmlspecialchars($collections_date) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "<input type='hidden' name='single_collection_posted' value='single_collection_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        return $out->addHTML($html);
    }

    protected function showEmptyPageTitlesError($button_name) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $out = $this->out;
        $user_name = $this->user_name;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= "<p>" . $this->msg('userpage-nocollections') . "</p>";
        $html .= "<p><a class='userpage-transparent' href='" . $article_url . "Special:NewManuscript'>" . $this->msg('userpage-newcollection') . "</a></p>";
        $html .= $this->getHTMLJavascriptLoaderGif();

        return $out->addHTML($html);
    }
    
    /**
     * This function displays a single collection (metadata and information on the pages) to the user
     */
    public function showSingleCollectionData($collection_title, $single_collection_data) {

        global $wgArticleUrl; 
        $out = $this->out;
        $user_name = $this->user_name;
        $article_url = $wgArticleUrl;
        list($meta_data, $pages_within_collection) = $single_collection_data;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
        
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";

        $html .= "<form id='userpage-editmetadata' action='" . $article_url . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='edit_metadata_posted' value='" . $this->msg('userpage-editmetadatabutton') . "'>";
        $html .= "<input type='hidden' name='single_collection' value='" . $collection_title . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        //redirect to Special:NewManuscript, and automatically have the current collection selected
        $html .= "<form id='userpage-addnewpage' action='" . $article_url . "Special:NewManuscript' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='addnewpage' title='" . $this->msg('userpage-newcollection') . "' value='Add New Page'>";
        $html .= "<input type='hidden' name='selected_collection' value='" . $collection_title . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "<h2 style='text-align: center;'>" . $this->msg('userpage-collection') . ": " . $collection_title . "</h2>";
        $html .= "<br>";
        $html .= "<h3>" . $this->msg('userpage-metadata') . "</h3>";

        $html .= $this->getHTMLCollectionMetaTable($out, $meta_data);

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

        foreach ($pages_within_collection as $single_page_data) {

            $manuscripts_url = isset($single_page_data['manuscripts_url']) ? $single_page_data['manuscripts_url'] : '';
            $manuscripts_title = isset($single_page_data['manuscripts_title']) ? $single_page_data['manuscripts_title'] : '';
            $manuscripts_date = isset($single_page_data['manuscripts_date']) ? $single_page_data['manuscripts_date'] : '';

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

        $html .= "<input type='hidden' name='edit_collection_posted' value = '" . $collection_title . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</table>";
        $html .= "</form>";
        $html .= "</div>";

        return $out->addHTML($html);
    }
    
    /**
     * This function constructs the edit form for editing metadata.
     * 
     * See https://www.mediawiki.org/wiki/HTMLForm/tutorial for information on the MediaWiki form builder
     */
    public function showEditCollectionMetadata($meta_data = array(), $collection_title, $single_collection_data, $link_back_to_manuscript_page, $error_message = '') {

        list($meta_data, $pages_within_collection) = $single_collection_data;
        $meta_data = $this->HTMLSpecialCharachtersArray($meta_data);

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

        $out = $this->out;
        $user_name = $this->user_name;
        
        $max_length = $this->max_string_formfield_length;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);
        
        $edit_token = $out->getUser()->getEditToken();

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";
        
        $html .= "<form class='summarypage-form' id='userpage-collection' action='" . $article_url . "Special:UserPage' method='post'>";
        $html .= "<input type='submit' class='button-transparent' value='" . $this->msg('userpage-goback') . "'>";
        $html .= "<input type='hidden' name='single_collection_posted' value='" . htmlspecialchars($collection_title) . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        
        $html .= "<h2>" . $this->msg('userpage-editmetadata') . " " . $collection_title . "</h2>";
        $html .= $this->msg('userpage-optional');
        $html .= "<br><br>";

        if (!empty($error_message)) {
            $html .= "<div class='error'>" . $error_message . "</div>";
        }

        $html .= "</div>";

        $out->addHTML($html);

        $descriptor = array();

        //important! These are posted as 'textfield', but will appear as 'wptextfield' in the request object ! 
        $descriptor['metadata_title'] = array(
          'label-message' => 'metadata-title',
          'class' => 'HTMLTextField',
          'default' => $metatitle,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_name'] = array(
          'label-message' => 'metadata-name',
          'class' => 'HTMLTextField',
          'default' => $metaauthor,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_year'] = array(
          'label-message' => 'metadata-year',
          'class' => 'HTMLTextField',
          'default' => $metayear,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_pages'] = array(
          'label-message' => 'metadata-pages',
          'class' => 'HTMLTextField',
          'default' => $metapages,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_category'] = array(
          'label-message' => 'metadata-category',
          'class' => 'HTMLTextField',
          'default' => $metacategory,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_produced'] = array(
          'label-message' => 'metadata-produced',
          'class' => 'HTMLTextField',
          'default' => $metaproduced,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_producer'] = array(
          'label-message' => 'metadata-producer',
          'class' => 'HTMLTextField',
          'default' => $metaproducer,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_editors'] = array(
          'label-message' => 'metadata-editors',
          'class' => 'HTMLTextField',
          'default' => $metaeditors,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_journal'] = array(
          'label-message' => 'metadata-journal',
          'class' => 'HTMLTextField',
          'default' => $metajournal,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_journalnumber'] = array(
          'label-message' => 'metadata-journalnumber',
          'class' => 'HTMLTextField',
          'default' => $metajournalnumber,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_translators'] = array(
          'label-message' => 'metadata-translators',
          'class' => 'HTMLTextField',
          'default' => $metatranslators,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_websource'] = array(
          'label-message' => 'metadata-websource',
          'class' => 'HTMLTextField',
          'default' => $metawebsource,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_id'] = array(
          'label-message' => 'metadata-id',
          'class' => 'HTMLTextField',
          'default' => $metaid,
          'maxlength' => $max_length,
        );

        $descriptor['metadata_notes'] = array(
          'type' => 'textarea',
          'labelmessage' => 'metadata-notes',
          'default' => $metanotes,
          'rows' => 20,
          'cols' => 20,
          'maxlength' => ($max_length * 10),
        );

        //in case the user was directed here from a manuscript page, send the link back to that manuscript page with the form
        if (!empty($link_back_to_manuscript_page)) {
            $descriptor['hidden'] = array(
              'type' => 'hidden',
              'name' => 'link_back_to_manuscript_page',
              'default' => $link_back_to_manuscript_page,
            );
        }

        $html_form = new HTMLForm($descriptor, $this->getContext());
        $html_form->setSubmitText($this->msg('metadata-submit'));
        $html_form->addHiddenField('single_collection', $collection_title);
        $html_form->addHiddenField('save_metadata_posted','save_metadata_posted');
        $html_form->setSubmitCallback(array('SpecialUserPage', 'processInput'));
        $html_form->show();
    }
    
    /**
     * This function shows a confirmation of the edit after submission of the form, in case the user has reached the page via the link on a manuscript page
     * 
     */
    public function prepareRedirectBackToManuscriptPageAfterEditMetadata($link_back_to_manuscript_page) {

        global $wgArticleUrl;
        $article_url = $wgArticleUrl;
        $user_name = $this->user_name;
        $out = $this->out;
        $html = "";

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $html .= $this->getHTMLUserPageMenuBar($edit_token, array('button', 'button', 'button-active'));
        $html .= $this->getHTMLJavascriptLoaderGif();

        $html .= "<div id='userpage-singlecollectionwrap'>";

        $html .= "<p>" . $this->msg('userpage-editcomplete') . "</p>";

        $html .= "<form id='userpage-linkback' action='" . $article_url . $link_back_to_manuscript_page . "' method='post'>";
        $html .= "<input type='submit' class='button-transparent' name='linkback' title='" . $this->msg('userpage-linkback1') . "' value='" . $this->msg('userpage-linkback2') . $link_back_to_manuscript_page . "'>";
        $html .= "</form>";

        $html .= "</div>";

        return $out->addHTML($html);
    }
    
}
