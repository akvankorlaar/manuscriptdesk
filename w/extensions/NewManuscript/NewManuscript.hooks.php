<?php

/**
 * This file is part of the ManuscriptDesk (github.com/akvankorlaar/manuscriptdesk)
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
 * This file incorporates work covered by the following copyright and
 * permission notice: 
 * 
 * Copyright (C) 2013 Richard Davis
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
 * @author Richard Davis <r.davis@ulcc.ac.uk>
 * @author Ben Parish <b.parish@ulcc.ac.uk>
 * @copyright 2013 Richard Davis
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
    private $paths;

    /**
     * Load the zoomviewer if the page is in edit mode. 
     */
    public function onEditPageShowEditFormInitial(EditPage $editPage, OutputPage &$out) {

        try {

            if (!$this->manuscriptIsInEditMode($out) || !$this->currentPageIsAValidManuscriptPage($out)) {
                return true;
            }

            $this->setPageData($out->getTitle()->getPrefixedURL());
            $html = $this->getHTMLIframeForZoomviewer($out);
            $out->addHTML($html);
            $out->addModuleStyles('ext.zoomviewercss');
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Check if current page is a valid manuscript page 
     */
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
     * Load the zoom viewer and other features if the page is in view mode
     */
    public function onMediaWikiPerformAction(OutputPage $out, Article $article, Title $title, User $user, WebRequest $request, MediaWiki $wiki) {

        try {

            if (!$this->manuscriptIsInViewMode($out) || !$this->currentPageIsAValidManuscriptPage($out)) {
                return true;
            }

            $this->setPageData($out->getTitle()->getPrefixedUrl());

            if ($this->userIsAllowedToViewThePage($user)) {

                //Format of get request: <full_url>?showoriginalimage=true=
                if ($request->getText('showoriginalimage') === 'true') {
                    return $this->redirectToOriginalImage($out);
                }

                $this->addHTMLToViewPage($user, $out);
                $this->user_has_view_permission = true;
            }

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * onMediaWikiPerformAction hook. Enables manuscripteditors to get manuscripts data using 'action=render'
     */
    public function onMediaWikiPerformRenderAction(OutputPage $out, Article $article, Title $title, User $user, WebRequest $request, MediaWiki $wiki) {
        if (!$this->manuscriptIsInRenderMode($out) || !$this->currentPageIsAValidManuscriptPage($out)) {
            return true;
        }

        $this->setPageData($out->getTitle()->getPrefixedUrl());

        if ($this->userIsAllowedToViewThePage($user)) {
            $this->user_has_view_permission = true;
        }

        return;
    }

    private function manuscriptIsInRenderMode(OutputPage $out) {
        $context = $out->getContext();
        if (Action::getActionName($context) !== 'render') {
            return false;
        }

        return true;
    }

    /**
     * MediaWiki onRawPageViewBeforeOutput hook. Prevents users that are not manuscripteditors to get page text using 'action=raw' 
     */
    public function onRawPageViewBeforeOutput(&$rawAction, &$text) {
        $out = $rawAction->getOutput();
        $this->setPageData($out->getTitle()->getPrefixedUrl());
        $user = $out->getUser();
        if ($this->userIsAllowedToViewThePage($user)) {
            return true;
        }

        $text = $this->getMessage('error-viewpermission');
        return true;
    }

    private function redirectToOriginalImage(OutputPage $out) {
        $paths = $this->paths;

        if (!$paths->originalImagesFullPathIsConstructableFromScan()) {
            return true;
        }

        $web_link_initial_upload_path = $paths->getWebLinkOriginalImagesPath();
        return $out->redirect($web_link_initial_upload_path);
    }

    private function manuscriptIsInViewMode(OutputPage $out) {
        $context = $out->getContext();
        if (Action::getActionName($context) !== 'view') {
            return false;
        }

        return true;
    }

    /**
     * Set data for the current page based on $partial_url (manuscripts:name/manuscripttitle)
     */
    private function setPageData($partial_url) {
        $this->partial_url = $partial_url;
        $this->creator_user_name = $this->wrapper->getUserNameFromUrl($partial_url);
        $this->manuscripts_title = $this->wrapper->getManuscriptsTitleFromUrl($partial_url);
        $this->signature = $this->wrapper->getSignatureWrapper()->getManuscriptSignature($partial_url);
        $this->paths = ObjectRegistry::getInstance()->getNewManuscriptPaths($this->creator_user_name, $this->manuscripts_title);

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

    private function addHTMLToViewPage(User $user, OutputPage $out) {

        $html = '';

        if (isset($this->collection_title)) {
            $html .= $this->getHTMLCollectionHeader();
        }

        $html .= $this->getHTMLManuscriptViewLinks($user);
        $html .= $this->getHTMLIframeForZoomviewer($out);
        $out->addHTML($html);
        $out->addModuleStyles('ext.zoomviewercss');
        return;
    }

    /**
     * Table with links to original image, link to edit collection, link to previous page, link to next page 
     */
    private function getHTMLManuscriptViewLinks(User $user) {
        $html = "";
        $html .= "<table id='link-wrap'>";
        $html .= "<tr>";
        $html .= $this->getHTMLLinkToOriginalManuscriptImage($user);

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
     * Get the links to the previous and the next page of the collection, if they exist 
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
     * Return the link to the original image
     */
    private function getHTMLLinkToOriginalManuscriptImage(User $user) {

        $paths = $this->paths;
        $edit_token = $user->getEditToken();

        if (!$paths->originalImagesFullPathIsConstructableFromScan()) {
            return "<b>" . $this->getMessage('newmanuscripthooks-errorimage') . "</b>";
        }

        $web_link = $paths->getWebLinkOriginalImagesPath();

        $html = "";
        $html .= "<td>";
        $html .= "<form class='manuscriptpage-form' action='" . $web_link . "' method='post' target='_blank'>";
        $html .= "<input class='button-transparent' type='submit' name='editlink' value='" . $this->getMessage('newmanuscripthooks-originalimage') . "'>";
        $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
        $html .= "</form>";
        $html .= "</td>";
        return $html;
    }

    /**
     * Generate the HTML for the iframe
     */
    private function getHTMLIframeForZoomviewer(OutputPage $out) {
        global $wgScriptPath, $wgLang;
        $viewer_type = $this->getViewerType($out->getRequest());
        $viewer_path = $this->getViewerPath($viewer_type);
        $image_file_path = $this->constructImageFilePath();
        $language = $wgLang->getCode();
        $website_name = 'ManuscriptDesk';
        return '<iframe id="zoomviewerframe" src="' . $wgScriptPath . '/extensions/NewManuscript/' . $viewer_path . '?image=' . $image_file_path . '&amp;lang=' . $language . '&amp;sitename=' . urlencode($website_name) . '"></iframe>';
    }

    /**
     * Get the default viewer type. Is also changeable from the url. Format of get request: <full_url>?viewertype=zv=
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
            return 'js';
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
        $paths = $this->paths;
        $image_file_path = $paths->getWebLinkExportPath();
        $image_file_path = str_replace('?', '%3F', $image_file_path);
        return $image_file_path;
    }

    /**
     * Register the wikitext <pagemetatable> </pagemetatable>
     * with the parser, so that the metatable can be loaded. When these tags are encountered in the wikitext, the function renderPageMetaTable
     * is called. The metatable refers to meta data on a collection level, while the pagemetatable tags enable users to insert page-specific meta data
     */
    public static function register(Parser &$parser) {
        // Register the hook with the parser
        $parser->setHook('pagemetatable', array('NewManuscriptHooks', 'renderPageMetaTable'));
        return true;
    }

    /**
     * Render the pagemetatable, when the tags are encountered in the wikitext
     */
    public static function renderPageMetaTable($input, $args, Parser $parser) {
        $page_metatable = ObjectRegistry::getInstance()->getPageMetaTable();
        $page_metatable->setInputValuesFromTagContent($input);
        return $page_metatable->renderTable($input);
    }

    /**
     * Prevent users from moving a manuscript page
     */
    public function onAbortMove(Title $oldTitle, Title $newTitle, User $user, &$error, $reason) {

        if (!$this->isInManuscriptsNamespace($oldTitle)) {
            return true;
        }

        $error = $this->getMessage('newmanuscripthooks-move');

        return false;
    }

    /**
     * MediaWiki ArticleDelete hook. Runs every time mediawiki gets a delete request. Prevents
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
        $paths = $this->paths;
        $paths->setExportPaths();
        $paths->setPartialUrl();
        $delete_wrapper = ObjectRegistry::getInstance()->getManuscriptDeskDeleteWrapper();
        $delete_wrapper->setUserName($this->creator_user_name);
        $deleter = ObjectRegistry::getInstance()->getManuscriptDeskDeleter();
        $deleter->setNewManuscriptPaths($this->paths);
        $deleter->setCollectionTitle($this->collection_title);
        $deleter->deleteManuscriptPage();
        return;
    }

    /**
     * MediaWiki PageContentSave hook. Prevents users from saving new wiki pages on NS_MANUSCRIPTS when there is no corresponding file in the database,
     * and checks if the content is not larger than $max_charachters_manuscript  
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
        $paths = $this->paths;
        if (!$paths->originalImagesFullPathIsConstructableFromScan()) {
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
     * MediaWiki BeforePageDisplay hook. Adds additional modules containing CSS before the page is displayed
     */
    public function onBeforePageDisplay(OutputPage &$out, Skin &$ski) {

        try {

            $partial_url = $out->getTitle()->mPrefixedText;

            if ($this->isInManuscriptsNamespace($out) && $this->manuscriptIsInViewMode($out) && $this->user_has_view_permission) {
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
        $wrapper = ObjectRegistry::getInstance()->getAllCollectionsWrapper();
        return $wrapper->getSingleCollectionMetadata($collection_title);
    }

    /**
     * MediaWiki OutputPageParserOutput hook. Show the text if the user has permission, or show an error
     */
    public function onOutputPageParserOutput(OutputPage &$out, ParserOutput $parser_output) {

        if (!$this->isInManuscriptsNamespace($out)) {
            return true;
        }

        if (!$this->user_has_view_permission) {
            $parser_output->setText($this->getMessage('error-viewpermission'));
        }
        else {
            $this->visualiseStrayTagsAndRemoveNotSupportedTags($parser_output);
        }

        return true;
    }

    /**
     * Visualize <add> and <del> tags that are nested in themselves correctly. Remove tags that are not available in the editor for visualization.
     * These tags will still be visible in the editor. 
     */
    private function visualiseStrayTagsAndRemoveNotSupportedTags(ParserOutput $parser_output) {

        $text = $parser_output->getText();

        //look for stray </add> tags, and replace them with a tei-add span element  
        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/add&gt;/', '</span></span><span class="tei-add">$1</span>', $text);

        //look for stray </del> tags, and replace them with a tei-del span element  
        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/del&gt;/', '</span></span><span class="tei-del">$1</span>', $text);

        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/hi&gt;/', '</span></span><span class="tei-hi superscript">$1</span>', $text);

        //look for any other escaped tags, and remove them
        $text = preg_replace('/&lt;(.*?)&gt;/s', '', $text);

        $parser_output->setText($text);

        return true;
    }

}
