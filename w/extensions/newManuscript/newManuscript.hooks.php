<?php

/**
 * This file is part of the newManuscript extension
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
 */
class NewManuscriptHooks extends ManuscriptDeskBaseHooks {

    use HTMLCollectionMetaTable;

    /**
     * This is the newManuscriptHooks class for the NewManuscript extension. Various aspects relating to interacting with 
     * the manuscript page (and other special pages in the extension)are arranged here, 
     * such as loading the zoomviewer, loading the metatable, adding CSS modules, loading the link to the original image, 
     * making sure a manuscript page can be deleted only by the user that has uploaded it (unless the user is a sysop), and preventing users from making
     * normal wiki pages on NS_MANUSCRIPTS (the manuscripts namespace identified by 'manuscripts:' in the URL)
     */
    private $creator_user_name;
    private $manuscripts_title;
    private $collection_title;
    private $partial_url;
    private $out;
    private $user;
    private $title;
    private $wrapper;

    public function __construct() {
        
    }

    /**
     * This function loads the zoomviewer if the editor is in edit mode. 
     */
    public function onEditPageShowEditFormInitial(EditPage $editPage, OutputPage &$out) {

        if (!$this->manuscriptIsInEditMode() || !$this->currentPageIsAValidManuscriptPage()) {
            return true;
        }

        try {
            $this->setOutputPage($out);
            $this->setPageData($out->getTitle()->getPartialURL());
            $html = $this->getHTMLIframeForZoomviewer();
            $out->addHTML($html);
            $out->addModuleStyles('ext.zoomviewer');
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function currentPageIsAValidManuscriptPage() {
        $out = $this->out;
        if (!$this->isInManuscriptsNamespace($out) || !$this->manuscriptPageExists($out)) {
            return false;
        }

        return true;
    }

    private function manuscriptIsInEditMode() {
        $out = $this->out;
        $request = $out->getRequest();
        $value = $request->getText('action');

        //submit action will only be true in case the user tries to save a page with too many charachters (see '$this->max_charachters_manuscript')
        if ($value !== 'edit' || $value !== 'submit') {
            return false;
        }

        return true;
    }

    /**
     * This function loads the zoomviewer if the page on which it lands is a manuscript,
     * and if the url is valid.     
     */
    public function onMediaWikiPerformAction(OutputPage $out, Article $article, Title $title, User $user, WebRequest $request, MediaWiki $wiki) {

        if (!$this->manuscriptisInViewMode($out) || !$this->currentUserIsAManuscriptEditor($user) || !$this->currentPageIsAValidManuscriptPage()) {
            return true;
        }

        try {
            $this->setPageObjects();
            $this->setPageData($out->getTitle()->getPrefixedUrl());

            $html = '';
            $html .= $this->getHTMLCollectionHeader();
            $html .= $this->getHTMLManuscriptViewLinks();
            $html .= $this->getHTMLIframeForZoomviewer();
            $out->addHTML($html);
            $out->addModuleStyles('ext.zoomviewer');
            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function manuscriptIsInViewMode(OutputPage $out) {
        $context = $out->getContext();
        if (Action::getActionName($context) !== 'view') {
            return false;
        }

        return true;
    }

    private function setPageObjects(OutputPage $out, User $user, Title $title) {
        $this->setOutputPage($out);
        $this->setUser($user);
        $this->setTitle($title);
    }

    private function setPageData($partial_url) {
        $this->setWrapper();
        $this->partial_url = $partial_url;
        $this->creator_user_name = $this->wrapper->getUserNameFromUrl($partial_url);
        $this->manuscripts_title = $this->wrapper->getManuscriptsTitleFromUrl($partial_url);

        $collection_title = $this->wrapper->getCollectionTitleFromUrl($partial_url);
        if ($this->collectionTitleIsValid($collection_title)) {
            return $this->collection_title = $collection_title;
        }

        return;
    }

    private function collectionTitleIsValid() {
        $collection_title = $this->collection_title;
        if (!isset($collection_title) || empty($collection_title) || $collection_title === 'none') {
            return false;
        }

        return true;
    }

    private function getHTMLManuscriptViewLinks() {
        $html = "";
        $html .= "<table id='link-wrap'>";
        $html .= "<tr>";
        $html .= $this->getHTMLLinkToOriginalManuscriptImage();
        $html .= $this->getHTMLLinkToEditCollection();
        $html .= $this->getHTMLPreviousNextPageLinks();
        $html .= "</tr>";
        $html .= "</table>";
        return $html;
    }

    private function getHTMLCollectionHeader() {
        if (isset($this->collection_title)) {
            return '<h2>' . htmlspecialchars($collection_title) . '</h2><br>';
        }

        return '';
    }

    private function currentUserIsTheOwnerOfThePage(User $user = null) {

        $user = isset($user) ? $user : $this->user;
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

    private function getHTMLLinkToEditCollection() {

        if ($this->currentUserIsTheOwnerOfThePage() && isset($this->collection_title)) {

            global $wgArticleUrl;

            $collection_title = $this->collection_title;
            $partial_url = $this->partial_url;
            $edit_token = $this->user->getEditToken();

            $html = "";
            $html .= '<form class="manuscriptpage-form" action="' . $wgArticleUrl . 'Special:UserPage" method="post">';
            $html .= "<input class='button-transparent' type='submit' name='editlink' value='Edit Collection Metadata'>";
            $html .= "<input type='hidden' name='collection_title' value='" . $collection_title . "'>";
            $html .= "<input type='hidden' name='link_back_to_manuscript_page' value='" . $partial_url . "'>";
            $html .= "<input type='hidden' name='edit_metadata_posted' value = 'edit_metadata_posted'>";
            $html .= "<input type='hidden' name='wpEditToken' value='$edit_token'>";
            $html .= "</form>";

            return $html;
        }

        return '';
    }

    /**
     * This function gets the links to the previous and the next page of the collection, if they exist 
     */
    private function getHTMLPreviousNextPageLinks() {

        if (isset($this->collection_title)) {

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

        return '';
    }

    /**
     * This function returns the link to the original image
     */
    private function getHTMLLinkToOriginalManuscriptImage() {

        $partial_original_image_path = $this->constructPartialOriginalImagePath();
        $full_original_image_path = $this->constructFullOriginalImagePath($partial_original_image_path);

        if (!$this->fullOriginalImagePathIsOk($full_original_image_path)) {
            return "<b>" . $this->getMessage('newmanuscripthooks-errorimage') . "</b>";
        }

        $image_file_name = basename($full_original_image_path) . PHP_EOL;

        $link_original_image_path = $partial_original_image_path . '/' . $image_file_name;
        return "<td><a class='link-transparent' href='$link_original_image_path' target='_blank'>" . $this->getMessage('newmanuscripthooks-originalimage') . "</a></td>";
    }

    /**
     * Construct the full path of the original image
     */
    private function constructPartialOriginalImagePath() {

        global $wgNewManuscriptOptions;

        $original_images_dir = $wgNewManuscriptOptions['original_images_dir'];
        $creator_user_name = $this->creator_user_name;
        $manuscripts_title = $this->manuscripts_title;

        return $original_images_dir . '/' . $creator_user_name . '/' . $manuscripts_title;
    }

    /**
     * This function checks if the file is an image. This has been done earlier and more thouroughly when uploading, but these checks are just to make sure
     */
    private function isAllowedImage($path) {

        $allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];

        if (pathinfo($path, PATHINFO_EXTENSION) !== null) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            if (in_array($extension, $allowed_file_extensions) && getimagesize($path) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates the HTML for the iframe
     */
    private function getHTMLIframeForZoomviewer() {
        global $wgScriptPath, $wgLang;
        $viewer_type = $this->getViewerType();
        $viewer_path = $this->getViewerPath();
        $image_file_path = $this->constructImageFilePath();
        $language = $wgLang->getCode();
        $website_name = 'Manuscript Desk';
        return '<iframe id="zoomviewerframe" src="' . $wgScriptPath . '/extensions/NewManuscript/' . $viewer_path . '?image=' . $image_file_path . '&amp;lang=' . $language . '&amp;sitename=' . urlencode($website_name) . '"></iframe>';
    }

    /**
     * Get the default viewer type.
     */
    private function getViewerType() {

        if ($this->browserIsInternetExplorer()) {
            return 'js';
        }

        return 'zv';
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
        global $wgNewManuscriptOptions;
        $images_root_dir = $wgNewManuscriptOptions['zoomimages_root_dir'];
        return '/' . $images_root_dir . '/' . $this->creator_user_name . '/' . $this->manuscripts_title . '/';
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
        $this->subtractAlphabetNumbersTable();
    }

    private function deleteFilesAndDatabaseEntries() {
        $this->deleteZoomImageFiles();
        $this->deleteOriginalImage();

        $this->wrapper->deleteFromManuscripts($this->partial_url);

        if (isset($this->collection_title)) {
            $this->wrapper->checkAndDeleteCollectionifNeeded($this->collection_title);
        }

        return true;
    }

    private function subtractAlphabetNumbersTable() {
        $main_title_lowercase = $this->wrapper->getManuscriptsLowercaseTitle($this->partial_url);
        $alphabetnumbes_context = $this->determineAlphabetNumbersContextFromCollectionTitle();
        $this->wrapper->subtractAlphabetNumbers($main_title_lowercase, $alphabetnumbes_context);
    }

    private function determineAlphabetNumbersContextFromCollectionTitle() {
        if (!isset($this->collection_title)) {
            return 'SingleManuscriptPages';
        }
        else {
            return 'AllCollections';
        }
    }

    /**
     * Check if all the default files are present, and delete all files
     */
    private function deleteZoomImageFiles($zoom_image_files_path) {

        $zoom_image_files_path = $this->constructZoomImageFilesPath();

        if (!$this->zoomImagePathIsOk($zoom_image_files_path)) {
            return;
        }

        return $this->recursiveDeleteFromPath($zoom_image_files_path);
    }

    private function constructZoomImageFilesPath() {
        global $wgWebsiteRoot, $wgNewManuscriptOptions;
        $images_root_dir = $wgNewManuscriptOptions['images_root_dir'];
        $zoom_image_files_path = $wgWebsiteRoot . DIRECTORY_SEPARATOR . $images_root_dir . DIRECTORY_SEPARATOR . $this->creator_user_name . DIRECTORY_SEPARATOR . $this->manuscripts_title;
    }

    private function zoomImagePathIsOk($zoom_image_files_path) {
        $tile_group_url = $zoom_image_files_path . DIRECTORY_SEPARATOR . 'TileGroup0';
        $image_properties_url = $zoom_image_files_path . DIRECTORY_SEPARATOR . 'ImageProperties.xml';

        if (!is_dir($tile_group_url) || !is_file($image_properties_url)) {
            return false;
        }

        return true;
    }

    /**
     * This function checks if the original image path file is valid, and then calls deleteAllFiles()
     */
    private function deleteOriginalImage() {

        $partial_original_image_path = $this->constructPartialOriginalImagePath();
        $full_original_image_path = $this->constructFullOriginalImagePath($partial_original_image_path);

        if (!$this->fullOriginalImagePathIsOk()) {
            return;
        }

        return $this->recursiveDeleteFromPath($original_image_path);
    }

    private function constructFullOriginalImagePath($partial_original_image_path) {
        global $wgWebsiteRoot;

        $original_image_path = $wgWebsiteRoot . '/' . $partial_original_image_path;

        if (!is_dir($original_image_path)) {
            return '';
        }

        $file_scan = scandir($original_image_path);
        $image_file_name = isset($file_scan[2]) ? $file_scan[2] : "";

        if ($image_file_name === "") {
            return '';
        }

        return $original_image_path . '/' . $image_file_name;
    }

    private function fullOriginalImagePathIsOk($full_original_image_path) {

        if (empty($full_original_image_path) || !$this->isAllowedImage($full_original_image_path)) {
            return false;
        }

        return true;
    }

    private function recursiveDeleteFromPath($path) {

        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                //recursive call
                $this->recursiveDeleteFromPath(realpath($path) . DIRECTORY_SEPARATOR . $file);
            }

            return rmdir($path);
        }
        else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

    /**
     * This function prevents users from saving new wiki pages on NS_MANUSCRIPTS when there is no corresponding file in the database,
     * and it checks if the content is not larger than $max_charachters_manuscript  
     */
    public function onPageContentSave(&$wikiPage, &$user, &$content, &$summary, $isMinor, $isWatch, $section, &$flags, &$status) {

        if (!$this->isInManuscriptsNamespace($wikiPage)) {
            return true;
        }

        //could also check if there is a corresponding image on server
        if (!$this->currentPageExists($wikiPage) && !$this->savePageWasRequested($user)) {
            $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-nopermission') . "."));
            return true;
        }

        global $wgNewManuscriptOptions;
        $max_charachters_manuscript = $wgNewManuscriptOptions['max_charachters_manuscript'];
        $number_of_charachters_new_save = strlen($content->mText);

        if ($this->textExceedsMaximumAllowedLength($number_of_charachters_new_save, $max_charachters_manuscript)) {
            $status->fatal(new RawMessage($this->getMessage('newmanuscripthooks-maxchar1') . " " . $number_of_charachters_new_save . " " .
                $this->getMessage('newmanuscripthooks-maxchar2') . " " . $max_charachters_manuscript . " " . $this->getMessage('newmanuscripthooks-maxchar3') . "."));
            return true;
        }

        return true;
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

            if ($this->isInManuscriptsNamespace($out)) {
                $out->addModuleStyles('ext.metatable');
                if ($this->manuscriptIsInViewMode($out)) {
                    $this->addMetatableToManuscriptsPage($out);
                }
            }
            elseif ($partial_url === 'Special:NewManuscript') {
                $out->addModuleStyles('ext.newmanuscriptcss');
                $out->addModules('ext.newmanuscriptloader');
            }

            return true;
        } catch (Exception $e) {
            return true;
        }
    }

    private function addMetatableToManuscriptsPage(OutputPage $out) {

        $this->setWrapper();
        $collection_title = $this->wrapper->getCollectionTitleFromUrl($partial_url);

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
    public function onParserAfterTidy(&$parser, &$text) {

        //look for stray </add> tags, and replace them with a tei-add span element  
        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/add&gt;/', '</span></span><span class="tei-add">$1</span>', $text);

        //look for stray </del> tags, and replace them with a tei-del span element  
        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/del&gt;/', '</span></span><span class="tei-del">$1</span>', $text);

        $text = preg_replace('/<\/span><\/span>(.*?)&lt;\/hi&gt;/', '</span></span><span class="tei-hi superscript">$1</span>', $text);

        //look for any other escaped tags, and remove them
        $text = preg_replace('/&lt;(.*?)&gt;/s', '', $text);

        return true;
    }

    private function setWrapper() {

        if (isset($this->wrapper)) {
            return;
        }

        return $this->wrapper = new NewManuscriptWrapper();
    }

    private function setOutputPage(OutputPage $out) {

        if (isset($this->out)) {
            return;
        }

        return $this->out = $out;
    }

    private function setUser(User $user) {

        if (isset($this->user)) {
            return;
        }

        return $this->user = $user;
    }

    private function setTitle(Title $title) {

        if (isset($this->title)) {
            return;
        }

        return $this->title = $title;
    }

}
