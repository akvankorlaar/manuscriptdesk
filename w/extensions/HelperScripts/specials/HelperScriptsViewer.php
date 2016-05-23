<?php

/**
 * This file is part of the Manuscript Desk (github.com/akvankorlaar/manuscriptdesk)
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
class HelperScriptsViewer extends ManuscriptDeskBaseViewer {

    use HTMLJavascriptLoader;

    /**
     * Construct HTML for the default page 
     */
    public function showDefaultPage($error_message = '') {

        global $wgArticleUrl;
        $out = $this->out;

        $out->setPageTitle($out->msg('helperscripts'));

        $html = '';
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $edit_token = $out->getUser()->getEditToken();

        $alphabetnumbers_message = $out->msg('alphabetnumbers-message');
        $delete_manuscripts_message = $out->msg('delete-manuscripts-message');

        $html .= '<form class="manuscriptdesk-form" action="' . $wgArticleUrl . 'Special:HelperScripts" method="post">';
        $html .= "<input type='submit' class='manuscriptdesk-submitbutton' name='update_alphabetnumbers_posted' value='$alphabetnumbers_message'>";
        $html .= "<input type='submit' class='manuscriptdesk-submitbutton' name='delete_manuscripts_posted' value='$delete_manuscripts_message'>";
        $html .= "<input type='hidden' name='default_page_posted' value='default_page_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= '</form>';

        $html .= "</div>";
        return $out->addHTML($html);
    }

    /**
     * Show form if user wants to delete all data 
     */
    public function showDeletionForm() {
        $out = $this->out;
        $max_length = $this->max_string_formfield_length;

        $out->setPageTitle($out->msg('helperscripts'));

        $html = '';
        $html .= $this->getHTMLJavascriptLoader();
        $html .= "<div class='javascripthide'>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $html .= "</div>";
        $out->addHTML($html);

        $descriptor = array();

        $descriptor['phrase'] = array(
          'label-message' => 'phrase-message',
          'class' => 'HTMLTextField',
          'type' => 'password',
          'maxlength' => ($max_length * 20),
        );

        $html_form = new HTMLForm($descriptor, $out->getContext());
        $html_form->setSubmitText($out->msg('delete-submit'));
        $html_form->addHiddenField('phrase_posted', 'phrase_posted');
        $html_form->setSubmitCallback(array('SpecialHelperScripts', 'processInput'));
        return $html_form->show();
    }

    /**
     * Show notification when chosen action is complete
     */
    public function showActionComplete() {
        $out = $this->out;
        $out->setPageTitle($out->msg('helperscripts'));
        $html = $out->msg('action-complete');
        return $out->addHTML($html);
    }

}
