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
class SpecialOriginalImages extends ManuscriptDeskImageApi {

    /**
     * Class used to retrieve when a user wants to see original images
     */
    public function __construct() {
        parent::__construct('OriginalImages');
    }

    protected function constructFilePath() {
        global $wgOriginalImagesPath;
        $partial_path = $wgOriginalImagesPath . '/' . $this->arguments;

        if (!is_dir($partial_path)) {
            throw new \Exception('error-request');
        }

        $file_scan = scandir($partial_path);

        if (!isset($file_scan[2]) || $file_scan[2] === "") {
            throw new \Exception('error-request');
        }

        return $this->file_path = $partial_path . '/' . $file_scan[2];
    }

    /**
     * Show the file. In case of this class the file will always be an image 
     */
    protected function showFile() {

        if (!isset($this->file_path) || !is_file($this->file_path)) {
            throw new \Exception('error-request');
        }
        else {
            $response = $this->getRequest()->response();
            $response->header('Content-Type: image/png');
            readfile($this->file_path);
        }

        return;
    }

}
