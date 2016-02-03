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
class StylometricAnalysisViewer {

    private $out;
    private $max_length = 50; 

    public function __construct(Outputpage $out) {
        $this->out = $out;
    }

    /**
     * This function adds html used for the gif loader image
     */
    private function addStylometricAnalysisLoader() {

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
    public function showForm1(array $user_collections, $error_message = '') {

        global $wgArticleUrl;

        $out = $this->out;
        $article_url = $wgArticleUrl;

        $out->setPageTitle($this->msg('stylometricanalysis-welcome'));

        $about_message = $this->msg('stylometricanalysis-about');
        $version_message = $this->msg('stylometricanalysis-version');
        $software_message = $this->msg('stylometricanalysis-software');
        $lastedit_message = $this->msg('stylometricanalysis-lastedit');

        $html = "<table id='stylometricanalysis-infobox'>";
        $html .= "<tr><th>$about_message</th></tr>";
        $html .= "<tr><td>$version_message</td></tr>";
        $html .= "<tr><td>$software_message <a href= '' target='_blank'>    </a>.</td></tr>";
        $html .= "<tr><td id='stylometricanalysis-td'><small>$lastedit_message</small></td></tr>";
        $html .= "</table>";

        $html .= "<p>" . $this->msg('stylometricanalysis-instruction1') . '</p>';

        $html .= "<div id='javascript-error'></div>";

        //display the error 
        if (!empty($error_message)) {
            $html .= "<div class = 'error'>" . $error_message . "</div>";
        }

        $html .= "<form id='stylometricanalysis-form' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<h3>" . $this->msg('stylometricanalysis-collectionheader') . "</h3>";

        $html .= "<table class='stylometricanalysis-table'>";

        $a = 0;
        $html .= "<tr>";

        foreach ($user_collections as $collection_name => $small_url_array) {

            if (($a % 4) === 0) {
                $html .= "</tr>";
                $html .= "<tr>";
            }

            $manuscripts_urls = $small_url_array['manuscripts_url'];
            $manuscripts_urls['collection_name'] = $collection_name;

            foreach ($manuscripts_urls as $index => &$url) {
                $url = htmlspecialchars($url);
            }

            //encode the array into json to be able to place it in the checkbox value
            $json_small_url_array = json_encode($manuscripts_urls);
            $manuscript_pages_within_collection = htmlspecialchars(implode(', ', $small_url_array['manuscripts_title']));
            $collection_text = $this->msg('stylometricanalysis-contains') . $manuscript_pages_within_collection . '.';

            //add a checkbox for the collection
            $html .="<td>";
            $html .="<input type='checkbox' class='stylometricanalysis-checkbox' name='collection$a' value='$json_small_url_array'>" . htmlspecialchars($collection_name);
            $html .= "<br>";
            $html .= "<span class='stylometricanalysis-span'>" . $collection_text . "</span>";
            $html .="</td>";
            $a = ++$a;
        }

        $html .= "</tr>";
        $html .= "</table>";

        $html .= "<br><br>";

        $submit_hover_message = $this->msg('stylometricanalysis-hover');
        $submit_message = $this->msg('stylometricanalysis-submit');

        $edit_token = $out->getUser()->getEditToken();

        $html .= "<input type='submit' disabled id='stylometricanalysis-submitbutton' title = $submit_hover_message value=$submit_message>";
        $html .= "<input type='hidden' name='form1Posted' value='form1Posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";

        $html .="</form>";
        $html .= "<br>";

        $html .= $this->addStylometricAnalysisLoader();

        $out->addHTML($html);

        return true;
    }

    /**
     * This function constructs and shows the stylometric analysis form
     */
    public function showForm2(array $collection_array, RequestContext $context, $error_message = '') {

        global $wgArticleUrl;

        $article_url = $wgArticleUrl;
        $max_length = $this->max_length;
        $out = $this->out;

        $collection_name_array = array();

        foreach ($collection_array as $index => $small_url_array) {
            $collection_name_array[] = $small_url_array['collection_name'];
        }

        $collections_message = implode(', ', $collection_name_array) . ".";

        $out->setPageTitle($this->msg('stylometricanalysis-options'));

        $html = "";
        $html .= "<div id='stylometricanalysis-wrap'>";
        $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Go Back'>Go Back</a>";
        $html .= "<br><br>";
        $html .= $this->msg('stylometricanalysis-chosencollections') . $collections_message . "<br>";
        $html .= $this->msg('stylometricanalysis-chosencollection2');
        $html .= "<br><br>";

        //display the error 
        if (!empty($error_message)) {
            $html .= "<div class = 'error'>" . $error_message . "</div>";
        }

        $html .= "</div>";

        $html .= $this->addStylometricAnalysisLoader();

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
          'size' => 5, //display size
          'maxlength' => 5, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['maximumsize'] = array(
          'label' => 'Maximum Size',
          'class' => 'HTMLTextField',
          'default' => 10000,
          'size' => 5, //display size
          'maxlength' => 5, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['segmentsize'] = array(
          'label' => 'Segment Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => 5, //display size
          'maxlength' => 5, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['stepsize'] = array(
          'label' => 'Step Size',
          'class' => 'HTMLTextField',
          'default' => 0,
          'size' => 5, //display size
          'maxlength' => 5, //input size
          'section' => 'stylometricanalysis-section-preprocess',
        );

        $descriptor['removepronouns'] = array(
          'label' => 'Remove Pronouns',
          'class' => 'HTMLCheckField',
          'section' => 'stylometricanalysis-section-preprocess',
        );


        //add field for 'remove these items too'

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
          'size' => 5, //display size
          'maxlength' => 5, //input size
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['mfi'] = array(
          'label' => 'MFI',
          'class' => 'HTMLTextField',
          'default' => 100,
          'size' => 5, //display size
          'maxlength' => 5, //input size
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['minimumdf'] = array(
          'class' => 'HTMLTextField',
          'label' => 'Minimum DF',
          'default' => 0.00,
          'size' => 5,
          'maxlength' => 5,
          'section' => 'stylometricanalysis-section-feature',
        );

        $descriptor['maximumdf'] = array(
          'class' => 'HTMLTextField',
          'label' => 'Maximum DF',
          'default' => 0.90,
          'size' => 5,
          'maxlength' => 5,
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
            'Hierarchical Clustering' => 'hierarchicalclustering',
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
        $html_form->setSubmitText($this->msg('stylometricanalysis-submit'));
        $html_form->addHiddenField('collection_array', json_encode($collection_array));
        $html_form->addHiddenField('form2Posted', 'form2Posted');
        $html_form->setSubmitCallback(array('SpecialStylometricAnalysis', 'callbackForm2'));
        $html_form->show();

        return true;
    }

    /**
     * This function shows the output page after the stylometric analysis has completed
     */
    public function showResult(array $config_array, $time, $pystyl_output, $full_linkpath1, $full_linkpath2) {

        global $wgArticleUrl;
        
        $out = $this->out;
        $edit_token = $out->getUser()->getEditToken();
        $article_url = $wgARticleUrl;
                
        $out->setPageTitle($this->msg('stylometricanalysis-output'));
        
        $html = "";

        $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Perform New Analysis'>Perform New Analysis</a>";
        
        $html .= "<form class='' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";
        $html .= "<input type='submit' class='' name='save_current_page' title='Click to save page' value='save_current_page>";  
        $html .= "<input type='hidden' name='time' value=$time>";  
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>"; 

        $html .= "<div style='display:block;'>";

        $html .= "<div id='visualization-wrap1'>";
        $html .= "<h2>Analysis One </h2>";
        $html .= "<p>Information about the plot</p>";
        $html .= "<img src='" . $full_linkpath1 . "' alt='Visualization1' height='650' width='650'>";
        $html .= "</div>";

        $html .= "<div id='visualization-wrap2'>";
        $html .= "<h2>Analysis Two </h2>";
        $html .= "<p>Information about the plot</p>";
        $html .= "<img src='" . $full_linkpath2 . "' alt='Visualization2' height='650' width='650'>";
        $html .= "</div>";

        $html .= "</div>";

        $html .= "<div id='visualization-wrap3'>";
        $html .= "<h2>Analysis Variables</h2><br>";
        $html .= "Remove non-alpha:" . $config_array['removenonalpha'] . "<br>";
        $html .= "Lowercase:" . $config_array['lowercase'] . "<br>";
        $html .= "Tokenizer:" . $config_array['tokenizer'] . "<br>";
        $html .= "Minimum Size:" . $config_array['minimumsize'] . "<br>";
        $html .= "Maximum Size:" . $config_array['maximumsize'] . "<br>";
        $html .= "Segment Size:" . $config_array['segmentsize'] . "<br>";
        $html .= "Step Size:" . $config_array['stepsize'] . "<br>";
        $html .= "Remove Pronouns:" . $config_array['removepronouns'] . "<br>";
        $html .= "Vectorspace:" . $config_array['vectorspace'] . "<br>";
        $html .= "Featuretype:" . $config_array['featuretype'] . "<br>";
        $html .= "Ngram Size:" . $config_array['ngramsize'] . "<br>";
        $html .= "MFI:" . $config_array['mfi'] . "<br>";
        $html .= "Minimum DF:" . $config_array['minimumdf'] . "<br>";
        $html .= "Maximum DF:" . $config_array['maximumdf'];
        $html .= "</div>";

        $html .= "This is the output of Pystyl: $pystyl_output";

        $out->addHTML($html);

        return true;
    }

    public function showNoPermissionError() {
        $out = $this->out;
        $out->addHTML($this->msg('stylometricanalysis-nopermission'));
        return true;
    }

    public function showFewCollectionsError() {

        global $wgArticleUrl;

        $out = $this->out;
        $article_url = $wgArticleUrl;

        $html = "";
        $html .= $this->msg('stylometricanalysis-fewcollections');
        $html .= "<p><a class='stylometricanalysis-transparent' href='" . $article_url . "Special:NewManuscript'>Create a new collection</a></p>";

        $out->addHTML($html);
        return true;
    }

    /**
     * This function retrieves the message from the i18n file for String $identifier
     */
    public function msg($identifier) {
        return wfMessage($identifier)->text();
    }

}
