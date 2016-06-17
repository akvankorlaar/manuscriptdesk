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
class SpecialZoomImages extends ManuscriptDeskImageApi {

    /**
     * Class used to retrieve images used for the zoomviewer 
     */
    public function __construct() {
        parent::__construct('ZoomImages');
    }

    protected function constructFilePath() {
        global $wgZoomImagesPath;

        $image_arguments = $this->arguments;
        $partial_path = $wgZoomImagesPath . '/' . $this->arguments;

        return $this->file_path = $partial_path;
    }

    /**
     * Show the file to the browser. In case of this class the file can either be an XML file or an image 
     */
    protected function showFile() {

        if (!isset($this->file_path)) {
            throw new \Exception('error-request');
        }


        if (!is_file($this->file_path) && strpos($this->file_path, '.xml') === false) {
            throw new \Exception('error-request');
        }
        elseif (strpos($this->file_path, '.xml') !== false) {
            $response = $this->getRequest()->response();
            $file_path = substr($this->file_path, 0, strpos($this->file_path, "?")); //some parameters are sent along with the xml path. Only use everything until the question mark
            $response->header('Content-Type: text/xml');
            $xml = simplexml_load_file($file_path);
            $this->getOutput()->addHTML($xml->asXML());
        }
        else {
            $response = $this->getRequest()->response();
            $response->header('Content-Type: image/png');
            readfile($this->file_path);
        }

        return;
    }

}
