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
class StylometricAnalysisHooks extends ManuscriptDeskBaseHooks {

    /**
     * The MediaWikiPerformAction hook. Check whether the user is allowed to view the page, and load stylometric analysis data to show stylometric analysis namespace page 
     */
    public function onMediaWikiPerformAction($output, $article, $title, $user, $request, $wiki) {

        try {

            if (!$this->StylometricanalysisDataShouldBeLoaded($title, $request, $wiki)) {
                return true;
            }

            $wrapper = $this->wrapper;

            $partial_url = $title->getPrefixedUrl();

            $this->signature = $wrapper->getSignatureWrapper()->getStylometricAnalysisSignature($partial_url);

            if (!$this->userIsAllowedToViewThePage($user)) {
                return true;
            }

            $this->user_has_view_permission = true;
            $data = $wrapper->getStylometricanalysisData($partial_url);

            $viewer = new StylometricAnalysisViewer($output);
            $viewer->showStylometricAnalysisNamespacePage($data);

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Check whether data should be loaded for the stylometric analysis namespace page 
     */
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
     * Prevent users from moving a stylometricanalysis page
     */
    public function onAbortMove(Title $oldTitle, Title $newTitle, User $user, &$error, $reason) {

        if ($oldTitle->getNamespace() === NS_STYLOMETRICANALYSIS) {
            $error = $this->getMessage('stylometricanalysis-move');

            return false;
        }

        return true;
    }

    /**
     * MediaWiki ArticleDelete hook. Prevent users from deleting stylometricanalysis pages they have not uploaded (except for sysops)
     */
    public function onArticleDelete(WikiPage &$wikiPage, User &$user, &$reason, &$error) {

        try {
            $title = $wikiPage->getTitle();

            if (!$this->isStylometricAnalysisNamespace($title)) {
                return true;
            }

            if (!$this->userIsAllowedToDeleteThePage($user, $title)) {
                $error = '<br>' . $this->getMessage('collatehooks-nodeletepermission') . '.';
                return false;
            }

            $wrapper = ObjectRegistry::getInstance()->getManuscriptDeskDeleteWrapper();
            $wrapper->setUser($user->getName());
            $deleter = ObjectRegistry::getInstance()->getManuscriptDeskDeleter();
            $deleter->deleteStylometricAnalysisData($title->getPrefixedURL());
        } catch (Exception $e) {
            return true;
        }

        return true;
    }

    /**
     * MediaWiki PaveContentSave hook. Prevent users from making any pages on NS_STYLOMETRICANALYSIS, if they are not creating this page
     * through the stylometricanalysis extension. 
     */
    public function onPageContentSave(WikiPage &$wikiPage, User &$user, Content &$content, &$summary, $isMinor, $isWatch, $section, &$flags, &$status) {

        try {

            if (!$this->isStylometricAnalysisNamespace($wikiPage)) {
                return true;
            }

            if (!$this->currentPageExists($wikiPage) && !$this->savePageWasRequested($user)) {
                $status->fatal(new RawMessage($this->getMessage('stylometricanalysishooks-nopermission')));
                return true;
            }

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Check if the current page is in NS_STYLOMETRICANALYSIS 
     */
    private function isStylometricAnalysisNamespace($object) {

        $namespace = $this->getNamespaceFromObject($object);

        if ($namespace !== NS_STYLOMETRICANALYSIS) {
            return false;
        }

        return true;
    }

    /**
     * MediaWiki ResourceLoaderGetConfigVars hook. Send configuration variables to javascript used for the button controller. In javascript they are accessed through 'mw.config.get('..') 
     */
    public function onResourceLoaderGetConfigVars(&$vars) {

        global $wgStylometricAnalysisOptions;

        $vars['wgmin_stylometricanalysis_collections'] = $wgStylometricAnalysisOptions['wgmin_stylometricanalysis_collections'];
        $vars['wgmax_stylometricanalysis_collections'] = $wgStylometricAnalysisOptions['wgmax_stylometricanalysis_collections'];

        return true;
    }

    /**
     * MediaWiki BeforePageDisplay hook. Loads additional modules containing CSS before the page is displayed
     */
    public function onBeforePageDisplay(OutputPage &$out, Skin &$ski) {

        $page_title_with_namespace = $out->getTitle()->getPrefixedURL();

        if ($page_title_with_namespace === 'Special:StylometricAnalysis') {

            $css_modules = array('ext.stylometricanalysiscss', 'ext.manuscriptdeskbasecss');
            $javascript_modules = array('ext.stylometricanalysisbuttoncontroller', 'ext.javascriptloader');
            $out->addModuleStyles($css_modules);
            $out->addModules($javascript_modules);
        }
        elseif ($this->isStylometricAnalysisNamespace($out)) {
            $out->addModuleStyles(array('ext.stylometricanalysiscss', 'ext.manuscriptdeskbasecss'));
        }

        return true;
    }

    /**
     * MediaWii OutputPageParserOutput hook. If user has no permission to view the page, show an error 
     */
    public function onOutputPageParserOutput(OutputPage &$out, ParserOutput $parser_output) {

        if (!$this->isStylometricAnalysisNamespace($out)) {
            return true;
        }

        if (!$this->user_has_view_permission) {
            $parser_output->setText($this->getMessage('error-viewpermission'));
        }

        return true;
    }

}
