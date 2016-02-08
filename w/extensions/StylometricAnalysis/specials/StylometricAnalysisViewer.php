<?php

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
class StylometricAnalysisViewer extends ManuscriptDeskBaseViewer {

    private $out;
    private $max_formfield_length = 5;

    public function __construct(Outputpage $out) {
        $this->out = $out;
    }

    /**
     * This function adds html used for the gif loader image
     */
    private function addStylometricAnalysisLoaderImage() {
        //shows after submit has been clicked
        $html = "<div id='stylometricanalysis-loaderdiv'>";
        $html .= "<img id='stylometricanalysis-loadergif' src='/w/extensions/collate/specials/assets/362.gif' style='width: 64px; height: 64px;"
            . " position: relative; left: 50%;'>";
        $html .= "</div>";
        return $html;
    }

    /**
     * This function constructs the HTML for the default page
     */
    public function showForm1(array $user_collection_data, $error_message = '') {

        global $wgArticleUrl;

        $out = $this->out;
        $article_url = $wgArticleUrl;
        $user_collection_data = $this->HTMLSpecialCharachtersArray($user_collection_data);

        $out->setPageTitle($out->msg('stylometricanalysis-welcome'));

        $about_message = $out->msg('stylometricanalysis-about');
        $version_message = $out->msg('stylometricanalysis-version');
        $software_message = $out->msg('stylometricanalysis-software');
        $lastedit_message = $out->msg('stylometricanalysis-lastedit');

        $html = "<table id='stylometricanalysis-infobox'>";
        $html .= "<tr><th>$about_message</th></tr>";
        $html .= "<tr><td>$version_message</td></tr>";
        $html .= "<tr><td>$software_message <a href= '' target='_blank'>Pystyl</a>.</td></tr>";
        $html .= "<tr><td id='stylometricanalysis-infobox-lasttd'><small>$lastedit_message</small></td></tr>";
        $html .= "</table>";

        $html .= "<p>" . $out->msg('stylometricanalysis-instruction1') . '</p>';

        $html .= "<div id='javascript-error'></div>";

        //display the error 
        if (!empty($error_message)) {
            $html .= "<div class = 'error'>" . $error_message . "</div>";
        }

        $html .= "<form id='stylometricanalysis-form' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<h3>" . $out->msg('stylometricanalysis-collectionheader') . "</h3>";

        $html .= "<table class='stylometricanalysis-table'>";

        $a = 0;
        $html .= "<tr>";

        foreach ($user_collection_data as $collection_name => $collection_data) {

            if (($a % 4) === 0) {
                $html .= "</tr>";
                $html .= "<tr>";
            }

            $collection_post_data = $collection_data['manuscripts_url'];
            $collection_post_data['collection_name'] = $collection_name;

            //encode the array into json to be able to place it in the checkbox value
            $json_collection_post_data = json_encode($collection_post_data);
            $manuscript_pages_within_collection = implode(', ', $collection_data['manuscripts_title']);
            $collection_text = $out->msg('stylometricanalysis-contains') . $manuscript_pages_within_collection . '.';

            //add a checkbox for the collection
            $html .="<td>";
            $html .="<input type='checkbox' class='stylometricanalysis-checkbox' name='collection$a' value='$json_collection_post_data'>" . $collection_name;
            $html .= "<br>";
            $html .= "<span class='stylometricanalysis-span'>" . $collection_text . "</span>";
            $html .="</td>";
            $a = ++$a;
        }

        $html .= "</tr>";
        $html .= "</table>";

        $html .= "<br><br>";

        $submit_hover_message = $out->msg('stylometricanalysis-hover');
        $submit_message = $out->msg('stylometricanalysis-submit');

        $edit_token = $out->getUser()->getEditToken();

        $html .= "<input type='submit' id='stylometricanalysis-submitbutton' title = $submit_hover_message value=$submit_message>";
        $html .= "<input type='hidden' name='form1Posted' value='form1Posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";

        $html .="</form>";
        $html .= "<br>";

        $html .= $this->addStylometricAnalysisLoaderImage();

        $out->addHTML($html);

        return true;
    }

    /**
     * This function constructs and shows the stylometric analysis form
     */
    public function showForm2(array $collection_array, array $collection_name_array, RequestContext $context, $error_message = '') {

        global $wgArticleUrl;

        $article_url = $wgArticleUrl;
        $max_formfield_length = $this->max_formfield_length;
        $out = $this->out;
        $collection_array = $this->HTMLSpecialCharachtersArray($collection_array);
        $collection_name_array = $this->HTMLSpecialCharachtersArray($collection_name_array);

        $collections_message = implode(', ', $collection_name_array) . ".";

        $out->setPageTitle($out->msg('stylometricanalysis-options'));

        $html = "";
        $html .= "<div id='stylometricanalysis-wrap'>";
        $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Go Back'>Go Back</a>";
        $html .= "<br><br>";
        $html .= $out->msg('stylometricanalysis-chosencollections') . $collections_message . "<br>";
        $html .= $out->msg('stylometricanalysis-chosencollection2');
        $html .= "<br><br>";

        //display the error 
        if (!empty($error_message)) {
            $html .= "<div class = 'error'>" . $error_message . "</div>";
        }

        $html .= "</div>";

        $html .= $this->addStylometricAnalysisLoaderImage();

        $out->addHTML($html);

        $descriptor = array();

        $descriptor['removenonalpha'] = array(
          'label' => 'Remove non-alpha',
          'class' => 'HTMLCheckField',
          'section' => 'stylometricanalysis-section-import',
        );

        $descriptor['lowercase'] = array(
          'label' => 'Lowercase',
          'class' => 'HTMLCheckField',
          'section' => 'stylometricanalysis-section-import',
        );

        $descriptor['tokenizer'] = array(
          'label' => 'Tokenizer',
          'class' => 'HTMLSelectField',
          'options' => array(
            'Whitespace' => 'whitespace',
            'Words' => 'words',
          ),
          'default' => 'whitespace',
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['minimumsize'] = array(
          'label' => 'Minimum Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => $max_formfield_length, 
          'maxlength' => $max_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['maximumsize'] = array(
          'label' => 'Maximum Size',
          'class' => 'HTMLTextField',
          'default' => 10000,
          'size' => $max_formfield_length, 
          'maxlength' => $max_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['segmentsize'] = array(
          'label' => 'Segment Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => $max_formfield_length, 
          'maxlength' => $max_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['stepsize'] = array(
          'label' => 'Step Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => $max_formfield_length, 
          'maxlength' => $max_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['removepronouns'] = array(
          'label' => 'Remove Pronouns',
          'class' => 'HTMLCheckField',
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['vectorspace'] = array(
          'label' => 'Vector Space',
          'class' => 'HTMLSelectField',
          'options' => array(
            'tf' => 'tf',
            'tf_scaled' => 'tf_scaled',
            'tf_std' => 'tf_std',
            'tf_idf' => 'tf_idf',
            'bin' => 'bin'
          ),
          'default' => 'tf',
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['featuretype'] = array(
          'label' => 'Feature Type',
          'class' => 'HTMLSelectField',
          'options' => array(
            'word' => 'word',
            'char' => 'char',
            'char_wb' => 'char_wb',
          ),
          'default' => 'word',
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['ngramsize'] = array(
          'label' => 'Ngram Size',
          'class' => 'HTMLTextField',
          'default' => 1,
          'size' => $max_formfield_length, 
          'maxlength' => $max_formfield_length, 
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['mfi'] = array(
          'label' => 'MFI',
          'class' => 'HTMLTextField',
          'default' => 100,
          'size' => $max_formfield_length, 
          'maxlength' => $max_formfield_length, 
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['minimumdf'] = array(
          'class' => 'HTMLTextField',
          'label' => 'Minimum DF',
          'default' => 0.00,
          'size' => $max_formfield_length,
          'maxlength' => $max_formfield_length,
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['maximumdf'] = array(
          'class' => 'HTMLTextField',
          'label' => 'Maximum DF',
          'default' => 0.90,
          'size' => $max_formfield_length,
          'maxlength' => $max_formfield_length,
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['visualization1'] = array(
          'label' => 'Visualization1',
          'class' => 'HTMLSelectField',
          'options' => array(
            'Hierarchical Clustering Dendrogram' => 'dendrogram',
            'PCA Scatterplot' => 'pcascatterplot',
            'TNSE Scatterplot' => 'tnsescatterplot',
            'Distance Matrix Clustering' => 'distancematrix',
            'Variability Based Neighbour Clustering' => 'neighbourclustering',
          ),
          'default' => 'dendrogram',
          'section' => 'stylometricanalysis-section-visualization',
        );

        $descriptor['visualization2'] = array(
          'label' => 'Visualization2',
          'class' => 'HTMLSelectField',
          'options' => array(
            'Hierarchical Clustering Dendrogram' => 'dendrogram',
            'PCA Scatterplot' => 'pcascatterplot',
            'TNSE Scatterplot' => 'tnsescatterplot',
            'Distance Matrix Clustering' => 'distancematrix',
            'Variability Based Neighbour Clustering' => 'neighbourclustering',
          ),
          'default' => 'dendrogram',
          'section' => 'stylometricanalysis-section-visualization',
        );

        $html_form = new HTMLForm($descriptor, $context);
        $html_form->setSubmitText($out->msg('stylometricanalysis-submit'));
        $html_form->addHiddenField('collection_array', json_encode($collection_array));
        $html_form->addHiddenField('form2Posted', 'form2Posted');
        $html_form->setSubmitCallback(array('SpecialStylometricAnalysis', 'callbackForm2'));
        $html_form->show();

        return true;
    }

    /**
     * This function shows the output page after the stylometric analysis has completed
     */
    public function showResult(array $config_array, array $collection_name_array, $full_linkpath1 = '', $full_linkpath2 = '', $time = null) {

        global $wgArticleUrl;

        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();
        $article_url = $wgArticleUrl;
        $config_array = $this->HTMLSpecialCharachtersArray($config_array);
        $collection_name_array = $this->HTMLSpecialCharachtersArray($collection_name_array);

        $removenonalpha = isset($config_array['removenonalpha']) ? $config_array['removenonalpha'] : '';
        $lowercase = isset($config_array['lowercase']) ? $config_array['lowercase'] : '';
        $tokenizer = isset($config_array['tokenizer']) ? $config_array['tokenizer'] : '';
        $minimumsize = isset($config_array['minimumsize']) ? $config_array['minimumsize'] : '';
        $maximumsize = isset($config_array['maximumsize']) ? $config_array['maximumsize'] : '';
        $segmentsize = isset($config_array['segmentsize']) ? $config_array['segmentsize'] : '';
        $stepsize = isset($config_array['stepsize']) ? $config_array['stepsize'] : '';
        $removepronouns = isset($config_array['removepronouns']) ? $config_array['removepronouns'] : '';
        $vectorspace = isset($config_array['vectorspace']) ? $config_array['vectorspace'] : '';
        $featuretype = isset($config_array['featuretype']) ? $config_array['featuretype'] : '';
        $ngramsize = isset($config_array['ngramsize']) ? $config_array['ngramsize'] : '';
        $mfi = isset($config_array['mfi']) ? $config_array['mfi'] : '';
        $minimumdf = isset($config_array['minimumdf']) ? $config_array['minimumdf'] : '';
        $maximumdf = isset($config_array['maximumdf']) ? $config_array['maximumdf'] : '';
        $visualization1 = isset($config_array['visualization1']) ? $config_array['visualization1'] : '';
        $visualization2 = isset($config_array['visualization2']) ? $config_array['visualization2'] : '';
        
        $imploded_collection_name_array = implode(', ', $collection_name_array);

        $out->setPageTitle($out->msg('stylometricanalysis-output'));
        
        $perform_new_analysis = $out->msg('stylometricanalysis-performnewanalysis');
        $save_button_value = $out->msg('stylometricanalysis-savevalue');
        $save_button_title = $out->msg('stylometricanalysis-savetitle');

        $html = "";
        $html .= "<div id='stylometricanalysis-buttons'>";
        
        $html .= "<form class='stylometricanalysis-form-two' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<input type='submit' id='stylometricanalysis-submitbutton-two' title='$perform_new_analysis' value='$perform_new_analysis'>";
        $html .= "<input type='hidden' name='redirect' value='redirect'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "<form class='stylometricanalysis-form-two' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<input type='submit' id='stylometricanalysis-submitbutton-two' title='$save_button_title' value='$save_button_value'>";
        $html .= "<input type='hidden' name='save_current_page' value='save_current_page'>";
        $html .= "<input type='hidden' name='time' value='$time'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        
        $html .= "</div>";
        
        $html .= "</br>";

        $html .= "<div id='visualization-wrap' style='display:block;'>";
             
        $html .= "<div id='visualization-wrap1'>";
        $html .= $out->msg('stylometricanalysis-collectionsused') . $imploded_collection_name_array;
        $html .= "<h2>" . ucfirst($visualization1) . "</h2>";
        $html .= "<img src='" . $full_linkpath1 . "' alt='Visualization1' height='650' width='650'>";
        $html .= "</div>";

        $html .= "<div id='visualization-wrap2'>";
        $html .= "<h2>" . ucfirst($visualization2) . "</h2>";
        $html .= "<img src='" . $full_linkpath2 . "' alt='Visualization2' height='650' width='650'>";
        $html .= "</div>";

        $html .= "</div>";

        $html .= "<div id='analysisconfiguration'>";
        $html .= "<h2>" . $out->msg('stylometricanalysis-analysisconfiguration') . "</h2><br>";
        $html .= "Remove non-alpha: " . $removenonalpha . "<br>";
        $html .= "Lowercase: " . $lowercase . "<br>";
        $html .= "Tokenizer: " . $tokenizer . "<br>";
        $html .= "Minimum Size: " . $minimumsize . "<br>";
        $html .= "Maximum Size: " . $maximumsize . "<br>";
        $html .= "Segment Size: " . $segmentsize . "<br>";
        $html .= "Step Size: " . $stepsize . "<br>";
        $html .= "Remove Pronouns: " . $removepronouns . "<br>";
        $html .= "Vectorspace: " . $vectorspace . "<br>";
        $html .= "Featuretype: " . $featuretype . "<br>";
        $html .= "Ngram Size: " . $ngramsize . "<br>";
        $html .= "MFI: " . $mfi . "<br>";
        $html .= "Minimum DF: " . $minimumdf . "<br>";
        $html .= "Maximum DF: " . $maximumdf;
        $html .= "</div>";
        
        $html .= $this->addStylometricAnalysisLoaderImage();

        $out->addHTML($html);
        return true;
    }

    public function showNoPermissionError($error_message = '') {
        $this->out->addHTML($error_message);
        return true;
    }

    public function showFewCollectionsError($error_message = '') {

        global $wgArticleUrl;

        $article_url = $wgArticleUrl;

        $html = "";
        $html .= $error_message; 
        $html .= "<p><a class='stylometricanalysis-transparent' href='" . $article_url . "Special:NewManuscript'>Create a new collection</a></p>";

        $this->out->addHTML($html);
        return true;
    }
}
