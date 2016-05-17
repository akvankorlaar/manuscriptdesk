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
abstract class ManuscriptDeskBaseViewer {

    protected $out;
    protected $max_int_formfield_length = 5;
    protected $max_string_formfield_length = 50;

    public function __construct(OutputPage $out) {
        $this->out = $out;
    }

    protected function HTMLSpecialCharachtersArray(array &$array) {
        foreach ($array as $index => &$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value);
            }

            if (is_array($value)) {
                $this->HTMLSpecialCharachtersArray($value);
            }
        }

        return $array;
    }

    public function showSimpleErrorMessage($error_message = '') {
        $this->out->addHTML($error_message);
        return;
    }

    public function showFewUploadsError($error_message) {
        $out = $this->out;
        $class = get_class($this);
        $message = '';

        switch ($class) {
            case 'CollateViewer':
                $message = $out->msg('collate-fewuploads');
                break;
            case 'StylometricAnalysisViewer':
                $message = $out->msg('stylometricanalysis-fewuploads');
                break;
        }

        $out->addHTML($message);
        return;
    }

}
