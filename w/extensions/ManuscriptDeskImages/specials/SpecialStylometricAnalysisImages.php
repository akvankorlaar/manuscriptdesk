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
 * 
 */
class SpecialStylometricAnalysisImages extends ManuscriptDeskImageApi {

    /**
     * Class used to retrieve images used for the zoomviewer 
     */
    public function __construct() {
        parent::__construct('StylometricAnalysisImages');
    }

    protected function constructFilePath() {
        global $wgStylometricAnalysisPath;

        $image_arguments = $this->arguments;
        $partial_path = $wgStylometricAnalysisPath . '/' . $this->arguments;

        return $this->file_path = $partial_path;
    }

    /**
     * Show the file to the browser. In case of this class the file has to be an .SVG (XML) file
     */
    protected function showFile() {

        if (!isset($this->file_path) || !is_file($this->file_path) || strpos($this->file_path, '.svg') === false) {
            throw new \Exception('error-request');
        }
        else {
            $response = $this->getRequest()->response();
            $response->header('Content-Type: image/svg+xml');       
            $test = file_get_contents($this->file_path);
            $this->getOutput()->addHTML($test);
            return; 
        }
    }

}
