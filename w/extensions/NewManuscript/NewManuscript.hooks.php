<?php

/**
 * This file is part of the NewManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License Version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 * 
 * @package MediaWiki
 * @subpackage Extensions
 * @author Arent van Korlaar <akvankorlaar'at' gmail 'dot' com> 
 * @copyright 2015 Arent van Korlaar
 * 
 * 
 * Todo: Who owns this file, who has copyright for it? Some of the functions are from Richard Davis ... 
 * This file incorporates work covered by the following copyright and
 * permission notice: 
 * 
 * Todo: Some of the variables that refer to the same things have different names. Fix this. 
 */
class NewManuscriptHooks extends ManuscriptDeskBaseHooks {

    use HTMLCollectionMetaTable;

    /**
     * This is the NewManuscriptHooks class for the NewManuscript extension. Various aspects relating to interacting with 
     * the manuscript page (and other special pages in the extension)are arranged here, 
     * such as loading the zoomviewer, loading the metatable, adding CSS modules, loading the link to the original image, 
     * making sure a manuscript page can be deleted only by the user that has uploaded it (unless the user is a sysop), and preventing users from making
     * normal wiki pages on NS_MANUSCRIPTS (the manuscripts namespace identified by 'manuscripts:' in the URL)
     */
    private $creator_user_name;
    private $manuscripts_title;
    private $collection_title;
    private $partial_url;
    private $wrapper;

    public function __construct(NewManuscriptWrapper $wrapper) {
        $this->wrapper = $wrapper;
    }

    /**
     * This function loads the zoomviewer if the editor is in edit mode. 
     */
    public function onEditPageShowEditFormInitial(EditPage $editPage, OutputPage &$out) {

        try {

            if (!$this->manuscriptIsInEditMode($out) || !$this->currentPageIsAValidManuscriptPage($out)) {
                return true;
            }

            $this->setPageData($out->getTitle()->getPrefixedURL());
            $html = $this->getHTMLIframeForZoomviewer($out->getRequest());
            $out->addHTML($html);
            $out->addModuleStyles('ext.zoomviewercss');
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function currentPageIsAValidManuscriptPage(OutputPage $out) {
        if (!$this->isInManuscriptsNamespace($out) || !$this->manuscriptPageExists($out)) {
            return false;
        }

        return true;
    }

    private function manuscriptIsInEditMode(OutputPage $out) {
        $request = $out->getRequest();
        $value = $request->getText('action');

        //submit action will only be true in case the user tries to save a page with too many charachters (see '$this->max_charachters_manuscript')
        if ($value !== 'edit' && $value !== 'submit') {
            return false;
        }

        return true;
    }

    /**
     * This function loads the zoomviewer if the page on which it lands is a manuscript,
     * and if the url is valid.     
     */
    public function onMediaWikiPerformAction(OutputPage $out, Article $article, Title $title, User $user, WebRequest $request, MediaWiki $wiki) {

        try {

            if (!$this->manuscriptisInViewMode($out) || !$this->currentUserIsAManuscriptEditor($user) || !$this->currentPageIsAValidManuscriptPage($out)) {
                return true;
            }

            $this->setPageData($out->getTitle()->getPrefixedUrl());

            //Format of get request: <full_url>?showoriginalimage=true=
            if ($request->getText('showoriginalimage') === 'true') {
                return $this->redirectToOriginalImage($out);
            }

            $html = '';

            if (isset($this->collection_title)) {
                $html .= $this->getHTMLCollectionHeader();
            }

            $html .= $this->getHTMLManuscriptViewLinks($user);
            $html .= $this->getHTMLIframeForZoomviewer($out->getRequest());
            $out->addHTML($html);
            $out->addModuleStyles('ext.zoomviewercss');
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function redirectToOriginalImage(OutputPage $out) {
        $paths = new NewManuscriptPaths($this->creator_user_name, $this->manuscripts_title);

        if (!$paths->initialUploadFullPathIsConstructableFromScan()) {
            return true;
        }

        $web_link_initial_upload_path = $paths->getWebLinkInitialUploadPath();
        return $out->redirect($web_link_initial_upload_path);
    }

    private function manuscriptIsInViewMode(OutputPage $out) {
        $context = $out->getContext();
        if (Action::getActionName($context) !== 'view') {
            return false;
        }

        return true;
    }

    private function setPageData($partial_url) {
        $this->partial_url = $partial_url;
        $this->creator_user_name = $this->wrapper->getUserNameFromUrl($partial_url);
        $this->manuscripts_title = $this->wrapper->getManuscriptsTitleFromUrl($partial_url);

        $collection_title = $this->wrapper->getCollectionTitleFromUrl($partial_url);
        if ($this->collectionTitleIsValid($collection_title)) {
            return $this->collection_title = $collection_title;
        }

        return;
    }

    private function collectionTitleIsValid($collection_title) {
        if (!isset($collection_title) || empty($collection_title) || $collection_title === 'none') {
            return false;
        }

        return true;
    }

    private function getHTMLManuscriptViewLinks(User $user) {
        $html = "";
        $html .= "<table id='link-wrap'>";
        $html .= "<tr>";
        $html .= $this->getHTMLLinkToOriginalManuscriptImage();

        if (isset($this->collection_title)) {

            if ($this->currentUserIsTheOwnerOfThePage($user)) {
                $html .= $this->getHTMLLinkToEditCollection($user);
            }

            $html .= $this->getHTMLPreviousNextPageLinks();
        }

        $html .= "</tr>";
        $html .= "</table>";
        return $html;
    }

    private function getHTMLCollectionHeader() {
        return '<h2>' . htmlspecialchars($this->collection_title) . '</h2><br>';
    }

    private function currentUserIsTheOwnerOfThePage(User $user) {
        $current_user_name = $user->getName();
        //only allow the owner of the collection to edit collection data
        if ($this->creator_user_name !== $current_user_name) {
            return false;
        }

        return true;
    }

    private function currentUserIsAManuscriptEditor(User $user) {
        if (!in_array('ManuscriptEditors', $user->getGroups())) {
            return false;
        }

        return true;
    }

    private function getHTMLLinkToEditCollection(User $user) {

        global $wgArticleUrl;

        $collection_title = $this->collection_title;
        $partial_url = $this->partial_url;
        $edit_token = $user->getEditToken();

        $html = "";
        $html .= "<td>";
        $html .= '<form class="manuscriptpage-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
        $html .= "<input class='button-transparent' type='submit' name='editlink' value='Edit Collection Metadata'>";
        $html .= "<input type='hidden' name='collection_title' value='" . $collection_title . "'>";
        $html .= "<input type='hidden' name='link_back_to_manuscript_page' value='" . $partial_url . "'>";
        $html .= "<input type='hidden' name='edit_metadata_posted' value = 'edit_metadata_posted'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        $html .= "</td>";

        return $html;
    }

    /**
     * This function gets the links to the previous and the next page of the collection, if they exist 
     */
    private function getHTMLPreviousNextPageLinks() {

        global $wgArticleUrl;

        $partial_url = $this->partial_url;
        $collection_title = $this->collection_title;
        list($previous_page_url, $next_page_url) = $this->wrapper->getPreviousAndNextPageUrl($collection_title, $partial_url);

        $html = "";
        $html .= "<td>";

        if (isset($previous_page_url)) {
            $html .= "<a href='" . $wgArticleUrl . htmlspecialchars($previous_page_url) . "' class='link-transparent' title='Go to Previous Page'>Go to Previous Page</a>";
        }

        if (isset($previous_page_url) && isset($next_page_url)) {
            $html .= "<br>";
        }

        if (isset($next_page_url)) {
            $html .= "<a href='" . $wgArticleUrl . htmlspecialchars($next_page_url) . "' class='link-transparent' title='Go to Next Page'>Go to Next Page</a>";
        }

        $html .= "</td>";
        return $html;
    }

    /**
     * This function returns the link to the original image
     */
    private function getHTMLLinkToOriginalManuscriptImage() {

        $paths = new NewManuscriptPaths($this->creator_user_name, $this->manuscripts_title);

        if (!$paths->initialUploadFullPathIsConstructableFromScan()) {
            return "<b>" . $this->getMessage('newmanuscripthooks-errorimage') . "</b>";
        }

        $web_link_initial_upload_path = $paths->getWebLinkInitialUploadPath();
        return "<td><a class='link-transparent' href='$web_link_initial_upload_path' target='_blank'>" . $this->getMessage('newmanuscripthooks-originalimage') . "</a></td>";
    }

    /**
     * Generates the HTML for the iframe
     */
    private function getHTMLIframeForZoomviewer(WebRequest $request) {
        global $wgScriptPath, $wgLang;
        $viewer_type = $this->getViewerType($request);
        $viewer_path = $this->getViewerPath($viewer_type);
        $image_file_path = $this->constructImageFilePath();
        $language = $wgLang->getCode();
        $website_name = 'ManuscriptDesk';
        return '<iframe id="zoomviewerframe" src="' . $wgScriptPath . '/extensions/NewManuscript/' . $viewer_path . '?image=' . $image_file_path . '&amp;lang=' . $language . '&amp;sitename=' . urlencode($website_name) . '"></iframe>';
    }

    /**
     * Get the default viewer type. Format of get request: <full_url>?viewertype=zv=
     */
    private function getViewerType(WebRequest $request) {

        if ($request->getText('viewertype') !== '') {
            if (strpos($request->getText('viewertype'), 'js') !== false) {
                return 'js';
            }
            else {
                return 'zv';
            }
        }
        else {
            if ($this->browserIsInternetExplorer()) {
                return 'js';
            }
            else {
                return 'zv';
            }
        }
    }

    /**
     * Determines whether the browser is Internet Explorer.
     */
    private function browserIsInternetExplorer() {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('/MSIE/i', $user_agent)) {
            return true;
        }

        return false;
    }

    private function getViewerPath($viewer_type) {
        if ($viewer_type === 'js') {
            return 'tools/ajax-tiledviewer/ajax-tiledviewer.php';
        }

        return 'tools/zoomify/zoomifyviewer.php';
    }

    /**
     * Constructs the full path of the image to be passed to the iframe
     */
    private function constructImageFilePath() {
        $paths = new NewManuscriptPaths($this->creator_user_name, $this->manuscripts_title);
        return $paths->getWebLinkExportPath();
    }

    /**
     * The function register, registers the wikitext <pagemetatable> </pagemetatable>
     * with the parser, so that the metatable can be loaded. When these tags are encountered in the wikitext, the function renderPageMetaTable
     * is called. The metatable refers to meta data on a collection level, while the pagemetatable tags enable users to insert page-specific meta data
     */
    public static function register(Parser &$parser) {
        // Register the hook with the parser
        $parser->setHook('pagemetatable', array('NewManuscriptHooks', 'renderPageMetaTable'));
        return true;
    }

    /**
     * This function renders the pagemetatable, when the tags are encountered in the wikitext
     */
    public static function renderPageMetaTable($input, $args, Parser $parser) {
        $page_meta_table = new PageMetaTableFromTags();
        $page_meta_table->extractOptions($input);
        return $page_meta_table->renderTable($input);
    }

    /**
     * This function prevents users from moving a manuscript page
     */
    public function onAbortMove(Title $oldTitle, Title $newTitle, User $user, &$error, $reason) {

        if (!$this->isInManuscriptsNamespace($oldTitle)) {
            return true;
        }

        $error = $this->getMessage('newmanuscripthooks-move');

        return false;
    }

    /**
     * This function runs every time mediawiki gets a delete request. This function prevents
     * users from deleting manuscripts they have not uploaded
     */
    public function onArticleDelete(WikiPage &$wiki_page, User &$user, &$reason, &$error) {

        try {
            if (!$this->isInManuscriptsNamespace($wiki_page)) {
                return true;
            }

            $this->setPageData($wiki_page->getTitle()->getPrefixedUrl());

            if (!$this->currentUserIsTheOwnerOfThePage($user) && !$this->currentUserIsASysop($user)) {
                //deny deletion because the current user did not create this manuscript, and the user is not an administrator
                $error = "<br>" . $this->getMessage('newmanuscripthooks-nodeletepermission') . ".";
                return false;
            }

            $this->deleteFilesAndDatabaseEntries();
            return true;
        } catch (Exception $e) {
            $error = "<br>" . $this->getMessage('newmanuscripthooks-nodeletepermission') . ".";
            return false;
        }
    }

    private function deleteFilesAndDatabaseEntries() {
        $paths = new NewManuscriptPaths($this->creator_user_name, $this->manuscripts_title);
        $paths->setExportPaths();
        $paths->setPartialUrl();
        $deleter = new NewManuscriptDeleter($this->wrapper, $paths, $this->collection_title);
        $deleter->execute();
        return;
    }

    /**
     * This function prevents users from saving new wiki pages on NS_MANUSCRIPTS when there is no corresponding file in the database,
     * and it checks if the content is not larger than $max_charachters_manuscript  
     */
    public function onPageContentSave(&$wikiPage, &$user, &$content, &$summary, $isMinor, $isWatch, $section, &$flags, &$status) {

        try {

            if (!$this->isInManuscriptsNamespace($wikiPage)) {
                return true;
            }

            if (!$this->currentPageExists($wikiPage) && !$this->newManuscriptIsValid($wikiPage, $user)) {
                $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-nopermission') . "."));
                return true;
            }

            $this->checkIfTextExceedsMaximumLength($content, $status);
            return true;
        } catch (Exception $e) {
            $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-nopermission') . "."));
            return true;
        }
    }

    private function newManuscriptIsValid(WikiPage $wikiPage, User $user) {
        if (!$this->validNewManuscriptWasCreated($wikiPage) || !$this->savePageWasRequested($user)) {
            return false;
        }

        return true;
    }

    private function validNewManuscriptWasCreated(WikiPage $wikiPage) {
        $this->setPageData($wikiPage->getTitle()->getPrefixedUrl());
        $paths = new NewManuscriptPaths($this->creator_user_name, $this->manuscripts_title);
        if (!$paths->initialUploadFullPathIsConstructableFromScan()) {
            return false;
        }

        return true;
    }

    private function checkIfTextExceedsMaximumLength($content, $status) {
        global $wgNewManuscriptOptions;
        $max_charachters_manuscript = $wgNewManuscriptOptions['max_charachters_manuscript'];
        $number_of_charachters_new_save = strlen($content->mText);

        if ($this->textExceedsMaximumAllowedLength($number_of_charachters_new_save, $max_charachters_manuscript)) {
            $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-maxchar1') . " " . $number_of_charachters_new_save . " " .
                $this->getMessage('newmanuscripthooks-maxchar2') . " " . $max_charachters_manuscript . " " . $this->getMessage('newmanuscripthooks-maxchar3') . "."));
            return;
        }

        return;
    }

    private function textExceedsMaximumAllowedLength($number_of_charachters_new_save, $max_charachters_manuscript) {
        if ($number_of_charachters_new_save > $max_charachters_manuscript) {
            return true;
        }

        return false;
    }

    /**
     * This function adds additional modules containing CSS before the page is displayed
     */
    public function onBeforePageDisplay(OutputPage &$out, Skin &$ski) {

        try {

            $partial_url = $out->getTitle()->mPrefixedText;

            if ($this->isInManuscriptsNamespace($out) && $this->manuscriptIsInViewMode($out)) {
                //doing this here ensures the table will be displayed at the bottom of the 
                $this->addMetatableToManuscriptsPage($out);
                $out->addModuleStyles('ext.manuscriptpagecss');
            }
            elseif ($partial_url === 'Special:NewManuscript') {
                $out->addModules('ext.javascriptloader');
                $out->addModules('ext.newmanuscriptbuttoncontroller');
                $out->addModuleStyles('ext.manuscriptdeskbasecss');
            }

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function addMetatableToManuscriptsPage(OutputPage $out) {

        $collection_title = $this->wrapper->getCollectionTitleFromUrl($this->partial_url);

        if (!$this->collectionTitleIsValid($collection_title)) {
            return;
        }

        $meta_data = $this->getCollectionMetadata($collection_title);
        $html = $this->getHTMLCollectionMetaTable($out, $meta_data);
        $out->addHTML($html);

        return;
    }

    private function getCollectionMetadata($collection_title) {
        $database_wrapper = new AllCollectionsWrapper();
        return $database_wrapper->getSingleCollectionMetadata($collection_title);
    }

    /**
     * This function visualizes <add> and <del> tags that are nested in themselves correctly. It also removes tags that are not available in the editor for visualization.
     * These tags will still be visible in the editor. 
     */
    public function onParserAfterTidy(Parser &$parser, &$text) {

        //look for stray </add> tags, and replace them with a tei-add span element  
        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/add&gt;/', '</span></span><span class="tei-add">$1</span>', $text);

        //look for stray </del> tags, and replace them with a tei-del span element  
        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/del&gt;/', '</span></span><span class="tei-del">$1</span>', $text);

        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/hi&gt;/', '</span></span><span class="tei-hi superscript">$1</span>', $text);

        //look for any other escaped tags, and remove them
        $text = preg_replace('/&lt;(.*?)&gt;/s', '', $text);

        return true;
    }

    /**
     * Includes the unit tests for stylometricanalysis into the unit test list
     */
    public function onUnitTestsList(&$files) {
        $files = array_merge($files, glob(__DIR__ . '/tests/*Test.php'));
        return true;
    }

}
