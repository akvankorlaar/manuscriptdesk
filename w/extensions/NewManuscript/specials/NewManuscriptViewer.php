<?php

/**
 * This file is part of the NewManuscript extension
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
 * 
 */
class NewManuscriptViewer extends ManuscriptDeskBaseViewer {
    
    use HTMLJavascriptLoader; 

    public function showDefaultPage($error_message, array $collections_current_user, $collection_title) {
        $out = $this->out; 
        $out->setPageTitle($out->msg('newmanuscript'));
        $html = $this->getHTMLJavascriptLoader();
        $out->addHTML($html);
        $collections_message = $this->constructCollectionsMessage($collections_current_user);
        $formatted_error_message = $this->constructFormattedErrorMessage($error_message);
        $this->getNewManuscriptForm($error_message, $collections_message, $collection_title)->show();
    }

    private function constructCollectionsMessage(array $collections_current_user) {

        $collections_current_user = $this->HTMLSpecialCharachtersArray($collections_current_user);

        if (!empty($collections_current_user)) {
            $collections_string = implode(', ', $collections_current_user);
            $collections_message = $this->out->msg('newmanuscript-collections') . $collections_string;
        }
        else {
            $collections_message = "";
        }

        return $collections_message;
    }

    private function getNewManuscriptForm($error_message, $collections_message, $collection_title) {
        $new_manuscript_form = new NewManuscriptUploadForm(new DerivativeContext($this->out->getContext()), $collections_message, $collection_title);
        $formatted_error_message = $this->formatErrorMessage($error_message);
        $new_manuscript_form->addPreText($formatted_error_message);
        $new_manuscript_form->setSubmitCallback(array('SpecialnewManuscript', 'showUploadError'));
        $new_manuscript_form->setSubmitText($this->out->msg('newmanuscript-submit'));
        $new_manuscript_form->setSubmitName('wpUpload');
        // Used message keys: 'accesskey-upload', 'tooltip-upload'
        $new_manuscript_form->setSubmitTooltip('upload');
        $new_manuscript_form->setId('mw-upload-form');
        $new_manuscript_form->addHiddenField('save_page_posted','save_page_posted');
        return $new_manuscript_form;
    }

    private function constructFormattedErrorMessage($error_message) {
        $formatted_error_message = '<h2>' . $this->out->msg('uploadwarning') . "</h2>\n" .
            '<div class="error">' . $error_message . "</div>\n";

        return $formatted_error_message;
    }
    
    private function formatErrorMessage($error_message){
       return  '<div class="javascripthide"><div class="error">' . $error_message . "</div></div>";
    }

}
