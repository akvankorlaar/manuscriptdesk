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
class ManuscriptDeskDeleter {

    private $wrapper;
    private $paths;
    private $collection_title;
    private $manuscripts_url;

    public function __construct(ManuscriptDeskDeleteWrapper $wrapper) {
        $this->wrapper = $wrapper;
    }

    public function setNewManuscriptPaths(NewManuscriptPaths $paths) {
        return $this->paths = $paths;
    }

    public function setCollectionTitle($collection_title) {
        return $this->collection_title = $collection_title;
    }

    public function setManuscriptsUrl($manuscripts_url) {
        return $this->manuscripts_url = $manuscripts_url;
    }

    public function deleteManuscriptPage() {
        $this->subtractAlphabetNumbersTableManuscriptPages();
        $this->deleteDatabaseEntiesManuscripts();
        $this->deleteFilesManuscripts();
        $this->deleteWikiPageIfNeeded();
        return;
    }

    /**
     * Delete data for collations. The page itself does not need to be deleted since this function will only be executed when MediaWiki's own deleter will run and will 
     * take care of this. See onArticleDelete
     */
    public function deleteCollationData($partial_url) {
        $wrapper = $this->wrapper;
        $collations_lowercase_title = $wrapper->getCollationsLowercaseTitle($partial_url);
        $wrapper->getAlphabetNumbersWrapper()->modifyAlphabetNumbersSingleValue($collations_lowercase_title, 'AllCollations', 'subtract');
        $wrapper->deleteFromCollations($partial_url);
        return;
    }

    /**
     * Delete data for stylometricanalyses. The page itself does not need to be deleted since this function will only be executed when MediaWiki's own deleter will run and will 
     * take care of this. See onArticleDelete
     */
    public function deleteStylometricAnalysisData($partial_url) {
        $wrapper = $this->wrapper;
        $stylometricanalysis_lowercase_title = $wrapper->getStylometricAnalysisLowercaseTitle($partial_url);
        $wrapper->getAlphabetNumbersWrapper()->modifyAlphabetNumbersSingleValue($stylometricanalysis_lowercase_title, 'AllStylometricAnalysis', 'subtract');
        $this->deleteStylometricAnalysisFiles($partial_url);
        $wrapper->deleteFromStylometricAnalysis($partial_url);
        return;
    }

    private function deleteStylometricAnalysisFiles($partial_url) {
        try {
            $wrapper = $this->wrapper;
            list($full_outputpath1, $full_outputpath2) = $this->wrapper->getStylometricAnalysisFullOutputPaths($partial_url);
            unlink($full_outputpath1);
            unlink($full_outputpath2);
            return;
        } catch (Exception $e) {
            return;
        }
    }

    private function subtractAlphabetNumbersTableManuscriptPages() {
        try {
            $partial_url = $this->paths->getPartialUrl();
            $collection_title = $this->collection_title;
            $main_title_lowercase = $this->wrapper->getManuscriptsLowercaseTitle($partial_url);
            $alphabetnumbes_context = $this->wrapper->getAlphabetNumbersWrapper()->determineAlphabetNumbersContextFromCollectionTitle($collection_title);
            $this->wrapper->getAlphabetNumbersWrapper()->modifyAlphabetNumbersSingleValue($main_title_lowercase, $alphabetnumbes_context, 'subtract');
            return; 
        } catch (Exception $e) {
            return;
        }
    }

    private function deleteDatabaseEntiesManuscripts() {
        $partial_url = $this->paths->getPartialUrl();
        $collection_title = $this->collection_title;
        $status = $this->wrapper->deleteFromManuscripts($partial_url);

        if ($collection_title !== 'none') {
            $this->wrapper->checkAndDeleteCollectionifNeeded($this->collection_title);
        }

        return;
    }

    private function deleteFilesManuscripts() {
        $paths = $this->paths;
        if ($paths->originalImagesFullPathIsConstructableFromScan()) {
            $this->deleteInitialUploadFullPath();
        }

        return $this->deleteSlicerExportFiles();
    }

    private function deleteInitialUploadFullPath() {
        $paths = $this->paths;
        $initial_upload_full_path = $paths->getOriginalImagesFullPath();

        if (!$paths->isAllowedImage($initial_upload_full_path)) {
            return;
        }

        $initial_upload_base_path = $paths->getOriginalImagesBasePath();
        return $this->recursiveDeleteFromPath($initial_upload_base_path);
    }

    /**
     * Delete all exported files in case something went wrong 
     */
    private function deleteSlicerExportFiles() {
        $this->deleteSliceDirectory();
        $this->deleteFullExportPathFilesManuscripts();
        return;
    }

    private function deleteSliceDirectory() {
        $paths = $this->paths;
        $slice_directory = $paths->getUserExportPath() . '/' . 'slice';

        //check if the temporary directory 'slice' exists. If it does, it should be deleted. 
        if (file_exists($slice_directory)) {
            $this->recursiveDeleteFromPath($slice_directory);
        }

        return;
    }

    private function deleteFullExportPathFilesManuscripts() {
        $paths = $this->paths;
        $full_export_path = $paths->getFullExportPath();
        $tile_group_url = $full_export_path . 'TileGroup0';
        $image_properties_url = $full_export_path . 'ImageProperties.xml';

        if (!is_dir($tile_group_url) || !is_file($image_properties_url)) {
            return;
        }

        return $this->recursiveDeleteFromPath($full_export_path);
    }

    private function recursiveDeleteFromPath($path) {

        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                //recursive call
                $this->recursiveDeleteFromPath(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }
        else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

    private function deleteWikiPageIfNeeded() {
        try {
            if (isset($this->manuscripts_url)) {
                $page_id = $this->wrapper->getPageId($this->manuscripts_url);
                $this->wrapper->deletePageFromId($page_id);
            }
        } catch (Exception $e) {
            return;
        }

        return;
    }

}
