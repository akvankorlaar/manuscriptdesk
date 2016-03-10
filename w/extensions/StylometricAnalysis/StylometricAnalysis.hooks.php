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
class StylometricAnalysisHooks extends ManuscriptDeskBaseHooks {

    public function onMediaWikiPerformAction($output, $article, $title, $user, $request, $wiki) {

        try {

            if (!$this->StylometricanalysisDataShouldBeLoaded($title, $request, $wiki)) {
                return true;
            }

            $page_title_with_namespace = $title->getPrefixedUrl();

            $database_wrapper = new StylometricAnalysisWrapper($user->getName());
            $data = $database_wrapper->getStylometricanalysisData($page_title_with_namespace);

            $viewer = new StylometricAnalysisViewer($output);
            $viewer->showStylometricAnalysisNamespacePage($data);

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function StylometricanalysisDataShouldBeLoaded(Title $title, WebRequest $request, MediaWiki $wiki) {

        if ($wiki->getAction($request) !== 'view') {
            return false;
        }

        $namespace = $title->getNamespace();

        if ($namespace !== NS_STYLOMETRICANALYSIS) {
            return false;
        }

        return true;
    }

    /**
     * This function prevents users from moving a stylometricanalysis page
     */
    public function onAbortMove(Title $oldTitle, Title $newTitle, User $user, &$error, $reason) {

        if ($oldTitle->getNamespace() === NS_STYLOMETRICANALYSIS) {
            $error = $this->getMessage('stylometricanalysis-move');

            return false;
        }

        return true;
    }

    /**
     * This function runs every time mediawiki gets a delete request. This function prevents
     * users from deleting stylometricanalysis pages they have not uploaded
     */
    public function onArticleDelete(WikiPage &$wikiPage, User &$user, &$reason, &$error) {

        $title = $wikiPage->getTitle();
        
        if (!$this->isStylometricAnalysisNamespace($title)) {
            return true;
        }

        if (!$this->currentUserCreatedThePage($title, $user) && !$this->currentUserIsASysop($user)) {
            $error = '<br>' . $this->getMessage('stylometricanalysishooks-nodeletepermission') . '.';
            return false;
        }

        try {
            $database_wrapper = new StylometricAnalysisWrapper($user->getName());
            $database_wrapper->deleteStylometricAnalysisDatabaseEntry($title_object->getPrefixedURL());
        } catch (Exception $e) {
            return true;
        }

        return true;
    }

    /**
     * This function prevents users from making any pages on NS_STYLOMETRICANALYSIS, if they are not creating this page
     * through the stylometricanalysis extension. 
     */
    public function onPageContentSave(WikiPage &$wikiPage, User &$user, Content &$content, &$summary, $isMinor, $isWatch, $section, &$flags, &$status) {

        if (!$this->isStylometricAnalysisNamespace($wikiPage)) {
            return true;
        }

        if (!$this->currentPageExists($wikiPage) && !$this->stylometricAnalysisSavePageWasRequested($user)) {
            $status->fatal(new RawMessage($this->getMessage('stylometricanalysishooks-nopermission')));
            return true;
        }

        return true;
    }

    private function isStylometricAnalysisNamespace($object) {

        $namespace = $this->getNamespaceFromObject($object);

        if ($namespace !== NS_STYLOMETRICANALYSIS) {
            return false;
        }

        return true;
    }

    private function stylometricAnalysisSavePageWasRequested(User $user) {
        $request = $user->getRequest();

        if (!$request->getText('save_page_posted')) {
            return false;
        }

        return true;
    }

    /**
     * This function sends configuration variables to javascript. In javascript they are accessed through 'mw.config.get('..') 
     */
    public function onResourceLoaderGetConfigVars(&$vars) {

        global $wgStylometricAnalysisOptions;

        $vars['wgmin_stylometricanalysis_collections'] = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];
        $vars['wgmax_stylometricanalysis_collections'] = $wgStylometricAnalysisOptions['wgmax_stylometricanalysis_collections'];

        return true;
    }

    /**
     * This function loads additional modules containing CSS before the page is displayed
     */
    public function onBeforePageDisplay(OutputPage &$out, Skin &$ski) {

        $page_title_with_namespace = $out->getTitle()->getPrefixedURL();

        if ($page_title_with_namespace === 'Special:StylometricAnalysis') {
            
            $css_modules = array('ext.stylometricanalysiscss', 'ext.manuscriptdeskbasecss');
            $javascript_modules = array('ext.stylometricanalysisbuttoncontroller','ext.javascriptloader');
            $out->addModuleStyles($css_modules);          
            $out->addModules($javascript_modules);   
            
        }elseif($this->isStylometricAnalysisNamespace($out)){
            $out->addModuleStyles('ext.stylometricanalysiscss');
        }
        
        return true;
    }

    /**
     * Includes the unit tests for stylometricanalysis into the unit test list
     */
    public function onUnitTestsList(&$files) {
        $files = array_merge($files, glob(__DIR__ . '/tests/phpunit/*Test.php'));
        return true;
    }

}
