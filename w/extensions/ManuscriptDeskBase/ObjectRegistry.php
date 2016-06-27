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
class ObjectRegistry {

    /**
     * Singleton class used to store and create instances of objects for a single request. This prevents making multiple instances of the same object, 
     * and gives the ability to call the objects anywhere in the script. The class can be instantiated using ObjectRegistry::getInstance(). 
     */
    private static $instance = null;

    /**
     * Collate classes 
     */
    private $collate_wrapper = null;
    private $collate_hooks = null;
    private $collate_request_processor = null;
    private $collate_viewer = null;
    private $collatex_converter = null;

    /**
     * NewManuscript classes 
     */
    private $newmanuscript_hooks = null;
    private $newmanuscript_wrapper = null;
    private $image_validator = null;
    private $newmanuscript_paths = null;
    private $slicer_executer = null;
    private $newmanuscript_request_processor = null;
    private $newmanuscript_viewer = null;

    /**
     * StylometricAnalysis classes 
     */
    private $stylometricanalysis_wrapper = null;
    private $stylometricanalysis_hooks = null;
    private $stylometricanalysis_viewer = null;
    private $stylometricanalysis_request_processor = null;

    /**
     * SummaryPage classes 
     */
    private $allcollections_wrapper = null;
    private $allcollections_viewer = null;
    private $allcollations_wrapper = null;
    private $allcollations_viewer = null;
    private $allstylometricanalysis_wrapper = null;
    private $allstylometricanalysis_viewer = null;
    private $singlemanuscriptpages_wrapper = null;
    private $singlemanuscriptpages_viewer = null;
    private $summarypage_request_processor = null;
    private $userpage_request_processor = null;
    private $userpage_collations_viewer = null;
    private $userpage_manuscripts_viewer = null;
    private $userpage_collections_viewer = null;
    private $userpage_stylometricanalysis_viewer = null;
    private $userpage_default_viewer = null; 

    /**
     * HelperScripts classes
     */
    private $helperscripts_viewer = null;
    private $helperscripts_request_processor = null;
    private $helperscripts_hooks = null;
    private $helperscripts_delete_wrapper = null;
    private $alphabetnumbers_updater = null;

    /**
     * Other classes 
     */
    private $signature_wrapper = null;
    private $alphabetnumbers_wrapper = null;
    private $text_processor = null;
    private $page_metatable = null;
    private $validator = null;
    private $manuscriptdesk_deleter = null;
    private $manuscriptdesk_delete_wrapper = null; 

    private function __construct() {
        
    }

    static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Collate Classes 
     */
    public function getCollateViewer(Outputpage $out) {
        if (is_null($this->collate_viewer)) {
            $this->collate_viewer = new CollateViewer($out);
        }
        return $this->collate_viewer;
    }

    public function getCollateHooks() {
        if (is_null($this->collate_hooks)) {
            $collate_wrapper = $this->getCollateWrapper();
            $this->collate_hooks = new CollateHooks($collate_wrapper);
        }
        return $this->collate_hooks;
    }

    public function getCollateWrapper() {
        if (is_null($this->collate_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->collate_wrapper = new CollateWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->collate_wrapper;
    }

    public function getCollateRequestProcessor(WebRequest $request) {
        if (is_null($this->collate_request_processor)) {
            $validator = $this->getManuscriptDeskBaseValidator();
            $this->collate_request_processor = new CollateRequestProcessor($request, $validator);
        }
        return $this->collate_request_processor;
    }

    public function getCollatexConverter() {
        if (is_null($this->collatex_converter)) {
            $this->collatex_converter = new CollatexConverter();
        }
        return $this->collatex_converter;
    }

    /**
     * NewManuscript classes 
     */
    public function getNewManuscriptHooks() {
        if (is_null($this->newmanuscript_hooks)) {
            $wrapper = $this->getNewManuscriptWrapper();
            $this->newmanuscript_hooks = new NewManuscriptHooks($wrapper);
        }
        return $this->newmanuscript_hooks;
    }

    public function getNewManuscriptWrapper() {
        if (is_null($this->newmanuscript_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->newmanuscript_wrapper = new NewManuscriptWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->newmanuscript_wrapper;
    }

    public function getImageValidator(WebRequest $request) {
        if (is_null($this->image_validator)) {
            $this->image_validator = new NewManuscriptImageValidator($request);
        }
        return $this->image_validator;
    }

    public function getNewManuscriptPaths($user_name, $manuscripts_title, $extension = '') {
        if (is_null($this->newmanuscript_paths)) {
            $this->newmanuscript_paths = new NewManuscriptPaths($user_name, $manuscripts_title, $extension);
        }
        return $this->newmanuscript_paths;
    }

    public function getSlicerExecuter() {
        if (is_null($this->slicer_executer)) {
            $this->ensure(isset($this->newmanuscript_paths), 'error-request');
            $this->slicer_executer = new SlicerExecuter($this->newmanuscript_paths);
        }
        return $this->slicer_executer;
    }

    public function getNewManuscriptRequestProcessor(WebRequest $request) {
        if (is_null($this->newmanuscript_request_processor)) {
            $validator = $this->getManuscriptDeskBaseValidator();
            $this->newmanuscript_request_processor = new NewManuscriptRequestProcessor($request, $validator);
        }
        return $this->newmanuscript_request_processor;
    }

    public function getNewManuscriptViewer(Outputpage $out) {
        if (is_null($this->newmanuscript_viewer)) {
            $this->newmanuscript_viewer = new NewManuscriptViewer($out);
        }
        return $this->newmanuscript_viewer;
    }

    /**
     * StylometricAnalysis classes 
     */
    public function getStylometricAnalysisWrapper() {
        if (is_null($this->stylometricanalysis_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->stylometricanalysis_wrapper = new StylometricAnalysisWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->stylometricanalysis_wrapper;
    }

    public function getStylometricAnalysisHooks() {
        if (is_null($this->stylometricanalysis_hooks)) {
            $wrapper = $this->getStylometricAnalysisWrapper();
            $this->stylometricanalysis_hooks = new StylometricAnalysisHooks($wrapper);
        }
        return $this->stylometricanalysis_hooks;
    }

    public function getStylometricAnalysisViewer(OutputPage $out) {
        if (is_null($this->stylometricanalysis_viewer)) {
            $this->stylometricanalysis_viewer = new StylometricAnalysisViewer($out);
        }
        return $this->stylometricanalysis_viewer;
    }

    public function getStylometricAnalysisRequestProcessor(WebRequest $request) {
        if (is_null($this->stylometricanalysis_request_processor)) {
            $validator = $this->getManuscriptDeskBaseValidator();
            $this->stylometricanalysis_request_processor = new StylometricAnalysisRequestProcessor($request, $validator);
        }
        return $this->stylometricanalysis_request_processor;
    }

    /**
     * SummaryPage classes 
     */
    public function getAllCollectionsWrapper() {
        if (is_null($this->allcollections_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->allcollections_wrapper = new AllCollectionsWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->allcollections_wrapper;
    }

    public function getAllCollectionsViewer(OutputPage $out) {
        if (is_null($this->allcollections_viewer)) {
            $this->allcollections_viewer = new AllCollectionsViewer($out);
        }
        return $this->allcollections_viewer;
    }

    public function getAllCollationsViewer(OutputPage $out) {
        if (is_null($this->allcollations_viewer)) {
            $this->allcollations_viewer = new AllCollationsViewer($out);
        }
        return $this->allcollations_viewer;
    }

    public function getAllCollationsWrapper() {
        if (is_null($this->allcollations_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->allcollations_wrapper = new AllCollationsWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->allcollations_wrapper;
    }

    public function getSummaryPageRequestProcessor(WebRequest $request) {
        if (is_null($this->summarypage_request_processor)) {
            $validator = $this->getManuscriptDeskBaseValidator();
            $this->summarypage_request_processor = new SummaryPageRequestProcessor($request, $validator);
        }
        return $this->summarypage_request_processor;
    }

    public function getAllStylometricAnalysisWrapper() {
        if (is_null($this->allstylometricanalysis_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->allstylometricanalysis_wrapper = new AllStylometricAnalysisWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->allstylometricanalysis_wrapper;
    }

    public function getAllStylometricAnalysisViewer(OutputPage $out) {
        if (is_null($this->allstylometricanalysis_viewer)) {
            $this->allstylometricanalysis_viewer = new AllStylometricAnalysisViewer($out);
        }
        return $this->allstylometricanalysis_viewer;
    }

    public function getSingleManuscriptPagesWrapper() {
        if (is_null($this->singlemanuscriptpages_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $signature_wrapper = $this->getSignatureWrapper();
            $this->singlemanuscriptpages_wrapper = new SingleManuscriptPagesWrapper($alphabetnumbers_wrapper, $signature_wrapper);
        }
        return $this->singlemanuscriptpages_wrapper;
    }

    public function getSingleManuscriptPagesViewer(OutputPage $out) {
        if (is_null($this->singlemanuscriptpages_viewer)) {
            $this->singlemanuscriptpages_viewer = new SingleManuscriptPagesViewer($out);
        }
        return $this->singlemanuscriptpages_viewer;
    }

    public function getUserPageDefaultViewer(OutputPage $out) {
        if (is_null($this->userpage_default_viewer)) {
            $this->userpage_default_viewer = new UserPageDefaultViewer($out);
        }
        return $this->userpage_default_viewer;
    }

    public function getUserPageRequestProcessor(WebRequest $request) {
        if (is_null($this->userpage_request_processor)) {
            $validator = $this->getManuscriptDeskBaseValidator();
            $this->userpage_request_processor = new UserPageRequestProcessor($request, $validator);
        }
        return $this->userpage_request_processor;
    }

    public function getUserPageCollationsViewer(OutputPage $out) {
        if (is_null($this->userpage_collations_viewer)) {
            $this->userpage_collations_viewer = new UserPageCollationsViewer($out);
        }
        return $this->userpage_collations_viewer;
    }

    public function getUserPageManuscriptsViewer(OutputPage $out) {
        if (is_null($this->userpage_manuscripts_viewer)) {
            $this->userpage_manuscripts_viewer = new UserPageManuscriptsViewer($out);
        }
        return $this->userpage_manuscripts_viewer;
    }

    public function getUserPageCollectionsViewer(OutputPage $out) {
        if (is_null($this->userpage_collections_viewer)) {
            $this->userpage_collections_viewer = new UserPageCollectionsViewer($out);
        }
        return $this->userpage_collections_viewer;
    }

    public function getUserPageStylometricAnalysisViewer(OutputPage $out) {
        if (is_null($this->userpage_stylometricanalysis_viewer)) {
            $this->userpage_stylometricanalysis_viewer = new UserPageStylometricAnalysisViewer($out);
        }
        return $this->userpage_stylometricanalysis_viewer;
    }

    /**
     * Other classes 
     */
    public function getManuscriptDeskBaseTextProcessor() {
        if (is_null($this->text_processor)) {
            $this->text_processor = new ManuscriptDeskBaseTextProcessor();
        }
        return $this->text_processor;
    }

    public function getManuscriptDeskDeleter() {
        if (is_null($this->manuscriptdesk_deleter)) {
            $delete_wrapper = $this->getManuscriptDeskDeleteWrapper();
            $this->manuscriptdesk_deleter = new ManuscriptDeskDeleter($delete_wrapper);
        }

        return $this->manuscriptdesk_deleter;
    }

    public function getManuscriptDeskDeleteWrapper() {
        if (is_null($this->manuscriptdesk_delete_wrapper)) {
            $alphabetnumbers_wrapper = $this->getAlphabetNumbersWrapper();
            $this->manuscriptdesk_delete_wrapper = new ManuscriptDeskDeleteWrapper($alphabetnumbers_wrapper);
        }
        return $this->manuscriptdesk_delete_wrapper;
    }

    public function getPageMetaTable() {
        if (is_null($this->page_metatable)) {
            $this->page_metatable = new PageMetaTable();
        }
        return $this->page_metatable;
    }

    private function getAlphabetNumbersWrapper() {
        if (is_null($this->alphabetnumbers_wrapper)) {
            $this->alphabetnumbers_wrapper = new AlphabetNumbersWrapper();
        }
        return $this->alphabetnumbers_wrapper;
    }

    public function getSignatureWrapper() {
        if (is_null($this->signature_wrapper)) {
            $this->signature_wrapper = new SignatureWrapper();
        }
        return $this->signature_wrapper;
    }

    public function getManuscriptDeskBaseValidator() {
        if (is_null($this->validator)) {
            $this->validator = new ManuscriptDeskBaseValidator();
        }
        return $this->validator;
    }

    public function getHelperScriptsHooks() {
        if (is_null($this->helperscripts_hooks)) {
            $this->helperscripts_hooks = new HelperScriptsHooks();
        }
        return $this->helperscripts_hooks;
    }

    /**
     * HelperScripts 
     */
    public function getHelperScriptsViewer(OutputPage $out) {
        if (is_null($this->helperscripts_viewer)) {
            $this->helperscripts_viewer = new HelperScriptsViewer($out);
        }
        return $this->helperscripts_viewer;
    }

    public function getHelperScriptsRequestProcessor(WebRequest $request) {
        if (is_null($this->helperscripts_request_processor)) {
            $validator = $this->getManuscriptDeskBaseValidator();
            $this->helperscripts_request_processor = new HelperScriptsRequestProcessor($request, $validator);
        }
        return $this->helperscripts_request_processor;
    }
    
    public function getHelperScriptsDeleteWrapper() {
        if (is_null($this->helperscripts_delete_wrapper)) {
            $delete_wrapper = $this->getManuscriptDeskDeleteWrapper();
            $this->helperscripts_delete_wrapper = new HelperScriptsDeleteWrapper($delete_wrapper);
        }
        return $this->helperscripts_delete_wrapper;
    } 

    public function getAlphabetNumbersUpdater() {
        if (is_null($this->alphabetnumbers_updater)) {
            $wrapper = $this->getAlphabetNumbersWrapper();
            $this->alphabetnumbers_updater = new AlphabetNumbersUpdater($wrapper);
        }
        return $this->alphabetnumbers_updater;
    }

    /**
     * Setters for testing purposes (injecting stubs/mocks)
     */
    public function setCollateHooks($object = null) {
        return $this->collate_hooks = $object;
    }

    public function setCollateWrapper($object = null) {
        return $this->collate_wrapper = $object;
    }

    public function setCollateRequestProcessor($object = null) {
        return $this->collate_request_processor = $object;
    }

    public function setSignatureWrapper($object = null) {
        return $this->signature_wrapper = $object;
    }

    public function setAlphabetNumbersWrapper($object = null) {
        return $this->alphabetnumbers_wrapper = $object;
    }

    public function setValidator($object = null) {
        return $this->validator = $object;
    }

    public function setCollatexConverter($object = null) {
        return $this->collatex_converter = $object;
    }

    public function setPaths($object = null) {
        return $this->newmanuscript_paths = $object;
    }

    public function setNewManuscriptRequestProcessor($object = null) {
        return $this->newmanuscript_request_processor = $object;
    }

    public function setNewManuscriptViewer($object = null) {
        return $this->newmanuscript_viewer = $object;
    }

    public function setNewManuscriptWrapper($object = null) {
        return $this->newmanuscript_wrapper = $object;
    }

    public function setManuscriptDeskDeleter($object = null) {
        return $this->manuscriptdesk_deleter = $object;
    }

    public function setAllCollectionsWrapper($object = null) {
        return $this->allcollections_wrapper = $object;
    }

    public function setStylometricAnalysisWrapper($object = null) {
        return $this->stylometricanalysis_wrapper = $object;
    }

    public function setStylometricAnalysisHooks($object = null) {
        return $this->stylometricanalysis_hooks = $object;
    }

    public function setStylometricAnalysisViewer($object = null) {
        return $this->stylometricanalysis_viewer = $object;
    }

    public function setAllCollationsWrapper($object = null) {
        return $this->allcollations_wrapper = $object;
    }

    public function setAllCollectionsViewer($object = null) {
        return $this->allcollections_viewer = $object;
    }

    public function setSummaryPageRequestProcessor($object = null) {
        return $this->summarypage_request_processor = $object;
    }

    public function setAllStylometricAnalysisViewer($object = null) {
        return $this->allstylometricanalysis_viewer = $object;
    }

    public function setAllStylometricAnalysisWrapper($object = null) {
        return $this->foo = $object;
    }

    public function setSingleManuscriptPagesWrapper($object = null) {
        return $this->singlemanuscriptpages_wrapper = $object;
    }

    public function setSingleManuscriptPagesViewer($object = null) {
        return $this->singlemanuscriptpages_viewer = $object;
    }

    public function setUserPageRequestProcessor($object = null) {
        return $this->userpage_request_processor = $object;
    }

    public function setUserPageCollationsViewer($object = null) {
        return $this->userpage_collations_viewer = $object;
    }

    public function setUserPageManuscriptsViewer($object = null) {
        return $this->userpage_manuscripts_viewer = $object;
    }

    public function setUserPageCollectionsViewer($object = null) {
        return $this->userpage_collections_viewer = $object;
    }

    public function setHelperScriptsViewer($object = null) {
        return $this->helperscripts_viewer = $object;
    }

    public function setAlphabetNumbersUpdater($object = null) {
        return $this->alphabetnumbers_updater = $object;
    }

    public function setUserPageStylometricAnalysisViewer($object = null) {
        return $this->userpage_stylometricanalysis_viewer = $object;
    }

    public function setHelperScriptsHooks($object = null) {
        return $this->helperscripts_hooks = $object;
    }

    private function ensure($expression, $message) {
        if (!$expression) {
            throw new \Exception($message);
        }
    }

}
