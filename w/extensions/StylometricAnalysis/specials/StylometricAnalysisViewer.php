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
    
    use HTMLJavascriptLoader; 

    /**
     * This function constructs the HTML for the default page
     */
    public function showDefaultPage($error_message = '', array $user_collection_data) {

        global $wgArticleUrl;

        $out = $this->out;
        $user_collection_data = $this->HTMLSpecialCharachtersArray($user_collection_data);

        $out->setPageTitle($out->msg('stylometricanalysis-welcome'));
        
        $html = '';
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";

        $about_message = $out->msg('stylometricanalysis-about');
        $version_message = $out->msg('stylometricanalysis-version');
        $software_message = $out->msg('stylometricanalysis-software');
        $lastedit_message = $out->msg('stylometricanalysis-lastedit');

        $html .= "<table id='stylometricanalysis-infobox'>";
        $html .= "<tr><th>$about_message</th></tr>";
        $html .= "<tr><td>$version_message</td></tr>";
        $html .= "<tr><td>$software_message <a href= '' target='_blank'>Pystyl</a>.</td></tr>";
        $html .= "<tr><td id='stylometricanalysis-infobox-lasttd'><small>$lastedit_message</small></td></tr>";
        $html .= "</table>";

        $html .= "<p>" . $out->msg('stylometricanalysis-instruction1') . '</p>';

        $html .= "<div class='javascripterror'></div>";

        //display the error 
        if (!empty($error_message)) {
            $html .= "<div class = 'error'>" . $error_message . "</div>";
        }

        $html .= "<form class='manuscriptdesk-form' action='" . $wgArticleUrl . "Special:StylometricAnalysis' method='post'>";
        $html .= "<h3>" . $out->msg('stylometricanalysis-collectionheader') . "</h3>";

        $html .= "<table class='stylometricanalysis-table'>";

        $a = 0;
        $html .= "<tr>";

        foreach ($user_collection_data as $collection_name => $single_collection_data) {

            if (($a % 4) === 0) {
                $html .= "</tr>";
                $html .= "<tr>";
            }

            $collection_post_data = $single_collection_data['manuscripts_url'];
            $collection_post_data['collection_name'] = $collection_name;

            //encode the array into json to be able to place it in the checkbox value
            $json_collection_post_data = json_encode($collection_post_data);
            $manuscript_pages_within_collection = implode(', ', $single_collection_data['manuscripts_title']);
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
        $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";

        $html .="</form>";
        $html .= "<br>";
        
        $html .= "</div>";

        $out->addHTML($html);

        return true;
    }

    /**
     * This function constructs and shows the stylometric analysis form
     */
    public function showForm2(array $collection_data, array $collection_name_data, RequestContext $context, $error_message = '') {

        global $wgArticleUrl;

        $article_url = $wgArticleUrl;
        $max_int_formfield_length = $this->max_int_formfield_length;
        $out = $this->out;
        $collection_data = $this->HTMLSpecialCharachtersArray($collection_data);
        $collection_name_data = $this->HTMLSpecialCharachtersArray($collection_name_data);

        $collections_message = implode(', ', $collection_name_data) . ".";

        $out->setPageTitle($out->msg('stylometricanalysis-options'));

        $html = "";
                      
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";
        
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
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['maximumsize'] = array(
          'label' => 'Maximum Size',
          'class' => 'HTMLTextField',
          'default' => 10000,
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['segmentsize'] = array(
          'label' => 'Segment Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['stepsize'] = array(
          'label' => 'Step Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length, //input size
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
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length,
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['mfi'] = array(
          'label' => 'MFI',
          'class' => 'HTMLTextField',
          'default' => 100,
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length,
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['minimumdf'] = array(
          'class' => 'HTMLTextField',
          'label' => 'Minimum DF',
          'default' => 0.00,
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length,
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['maximumdf'] = array(
          'class' => 'HTMLTextField',
          'label' => 'Maximum DF',
          'default' => 0.90,
          'size' => $max_int_formfield_length,
          'maxlength' => $max_int_formfield_length,
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
        $html_form->addHiddenField('collection_data', json_encode($collection_data));
        $html_form->addHiddenField('form_2_posted', 'form_2_posted');
        $html_form->setSubmitCallback(array('SpecialStylometricAnalysis', 'callbackForm2'));
        $html_form->show();

        return true;
    }

    /**
     * This function shows the output page after the stylometric analysis has completed
     */
    public function showResult(array $pystyl_config, array $collection_name_data, $full_linkpath1 = '', $full_linkpath2 = '', $time = null) {

        global $wgArticleUrl;

        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();
        $article_url = $wgArticleUrl;
        $pystyl_config = $this->HTMLSpecialCharachtersArray($pystyl_config);
        $collection_name_data = $this->HTMLSpecialCharachtersArray($collection_name_data);

        $removenonalpha = isset($pystyl_config['removenonalpha']) ? $pystyl_config['removenonalpha'] : '';
        $lowercase = isset($pystyl_config['lowercase']) ? $pystyl_config['lowercase'] : '';
        $tokenizer = isset($pystyl_config['tokenizer']) ? $pystyl_config['tokenizer'] : '';
        $minimumsize = isset($pystyl_config['minimumsize']) ? $pystyl_config['minimumsize'] : '';
        $maximumsize = isset($pystyl_config['maximumsize']) ? $pystyl_config['maximumsize'] : '';
        $segmentsize = isset($pystyl_config['segmentsize']) ? $pystyl_config['segmentsize'] : '';
        $stepsize = isset($pystyl_config['stepsize']) ? $pystyl_config['stepsize'] : '';
        $removepronouns = isset($pystyl_config['removepronouns']) ? $pystyl_config['removepronouns'] : '';
        $vectorspace = isset($pystyl_config['vectorspace']) ? $pystyl_config['vectorspace'] : '';
        $featuretype = isset($pystyl_config['featuretype']) ? $pystyl_config['featuretype'] : '';
        $ngramsize = isset($pystyl_config['ngramsize']) ? $pystyl_config['ngramsize'] : '';
        $mfi = isset($pystyl_config['mfi']) ? $pystyl_config['mfi'] : '';
        $minimumdf = isset($pystyl_config['minimumdf']) ? $pystyl_config['minimumdf'] : '';
        $maximumdf = isset($pystyl_config['maximumdf']) ? $pystyl_config['maximumdf'] : '';
        $visualization1 = isset($pystyl_config['visualization1']) ? $pystyl_config['visualization1'] : '';
        $visualization2 = isset($pystyl_config['visualization2']) ? $pystyl_config['visualization2'] : '';

        $imploded_collection_name_data = implode(', ', $collection_name_data);

        $out->setPageTitle($out->msg('stylometricanalysis-output'));

        $perform_new_analysis = $out->msg('stylometricanalysis-performnewanalysis');
        $save_button_value = $out->msg('stylometricanalysis-savevalue');
        $save_button_title = $out->msg('stylometricanalysis-savetitle');

        $html = "";
        
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";
        
        $html .= "<div id='stylometricanalysis-buttons'>";

        $html .= "<form id='stylometricanalysis-form-two' class='manuscriptdesk-form' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<input type='submit' id='stylometricanalysis-submitbutton-two' title='$perform_new_analysis' value='$perform_new_analysis'>";
        $html .= "<input type='hidden' name='redirect_posted' value='redirect_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "<form id='stylometricanalysis-form-three' class='manuscriptdesk-form' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<input type='submit' id='stylometricanalysis-submitbutton-two' title='$save_button_title' value='$save_button_value'>";
        $html .= "<input type='hidden' name='save_page_posted' value='save_page_posted'>";
        $html .= "<input type='hidden' name='time' value='$time'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";

        $html .= "</div>";

        $html .= "</br>";

        $html .= "<div id='visualization-wrap' style='display:block;'>";

        $html .= "<div id='visualization-wrap1'>";
        $html .= $out->msg('stylometricanalysis-collectionsused') . $imploded_collection_name_data;
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
        
        $html .= "</div>";

        $out->addHTML($html);
        return true;
    }

    public function showStylometricAnalysisNamespacePage(array $data) {

        $out = $this->out;

        $data = $this->HTMLSpecialCharachtersArray($data);

        $user_name = isset($data['user']) ? $data['user'] : '';
        $time = isset($data['time']) ? $data['time'] : '';
        $full_outputpath1 = isset($data['full_outputpath1']) ? $data['full_outputpath1'] : '';
        $full_outputpath2 = isset($data['full_outputpath2']) ? $data['full_outputpath2'] : '';
        $full_linkpath1 = isset($data['full_linkpath1']) ? $data['full_linkpath1'] : '';
        $full_linkpath2 = isset($data['full_linkpath2']) ? $data['full_linkpath2'] : '';
        $pystyl_config = isset($data['pystyl_config']) ? $data['pystyl_config'] : '';
        $collection_name_data = isset($data['collection_name_data']) ? $data['collection_name_data'] : '';
        $date = isset($data['date']) ? $data['date'] : '';

        $removenonalpha = isset($pystyl_config['removenonalpha']) ? $pystyl_config['removenonalpha'] : '';
        $lowercase = isset($pystyl_config['lowercase']) ? $pystyl_config['lowercase'] : '';
        $tokenizer = isset($pystyl_config['tokenizer']) ? $pystyl_config['tokenizer'] : '';
        $minimumsize = isset($pystyl_config['minimumsize']) ? $pystyl_config['minimumsize'] : '';
        $maximumsize = isset($pystyl_config['maximumsize']) ? $pystyl_config['maximumsize'] : '';
        $segmentsize = isset($pystyl_config['segmentsize']) ? $pystyl_config['segmentsize'] : '';
        $stepsize = isset($pystyl_config['stepsize']) ? $pystyl_config['stepsize'] : '';
        $removepronouns = isset($pystyl_config['removepronouns']) ? $pystyl_config['removepronouns'] : '';
        $vectorspace = isset($pystyl_config['vectorspace']) ? $pystyl_config['vectorspace'] : '';
        $featuretype = isset($pystyl_config['featuretype']) ? $pystyl_config['featuretype'] : '';
        $ngramsize = isset($pystyl_config['ngramsize']) ? $pystyl_config['ngramsize'] : '';
        $mfi = isset($pystyl_config['mfi']) ? $pystyl_config['mfi'] : '';
        $minimumdf = isset($pystyl_config['minimumdf']) ? $pystyl_config['minimumdf'] : '';
        $maximumdf = isset($pystyl_config['maximumdf']) ? $pystyl_config['maximumdf'] : '';
        $visualization1 = isset($pystyl_config['visualization1']) ? $pystyl_config['visualization1'] : '';
        $visualization2 = isset($pystyl_config['visualization2']) ? $pystyl_config['visualization2'] : '';

        $html = "";

        if (!empty($user_name) && !empty($date)) {
            $html .= "This page has been created by: " . $user_name . "<br> Date: " . $date . "<br> ";
        }

        $imploded_collection_name_data = implode(', ', $collection_name_data);

        $html .= "<div id='visualization-wrap' style='display:block;'>";

        $html .= "<div id='visualization-wrap1'>";
        $html .= $out->msg('stylometricanalysis-collectionsused') . $imploded_collection_name_data;
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

        $out->addHTML($html);
    }

}
