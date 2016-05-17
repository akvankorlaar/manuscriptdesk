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
abstract class ManuscriptDeskBaseSpecials extends SpecialPage {

    protected $user_name;
    protected $viewer;
    protected $wrapper;
    protected $request_processor;
    protected $error_identifier;

    public function __construct($page_name) {
        parent::__construct($page_name);
    }

    protected function setVariables() {
        $user = $this->getUser();
        $this->user_name = $user->getName();
        $this->setViewer();
        $this->setWrapper();
        $this->setRequestProcessor();
        return;
    }

    /**
     * Main entry point for Special Pages in the Manuscript Desk
     */
    public function execute($subpage_arguments) {

        try {
            $this->setVariables();
            $this->checkManuscriptDeskPermission();

            if ($this->request_processor->requestWasPosted()) {
                $this->processRequest();
                return true;
            }

            $this->getDefaultPage();
            return true;
        } catch (Exception $e) {
            $this->handleExceptions($e);
            return false;
        }
    }

    /**
     * This function checks if the user has the appropriate permissions
     */
    protected function checkManuscriptDeskPermission() {
        $user = $this->getUser();

        if (!in_array('ManuscriptEditors', $user->getGroups())) {
            throw new \Exception('error-nopermission');
        }

        return true;
    }

    protected function currentUserIsASysop() {
        $user = $this->getUser();
        if (!in_array('sysop', $user->getGroups())) {
            return false;
        }

        return true;
    }

    /**
     * Create a new wikipage and return a $local_url
     */
    public function createNewWikiPage($new_url, $content = '') {

        $content = empty($content) ? '<!--' . $this->msg('manuscriptdesk-newpage') . '-->' : $content;

        $title = $this->createTitleObjectNewPage($new_url);

        $local_url = $title->getLocalURL();
        $context = $this->getContext();
        $article = Article::newFromTitle($title, $context);

        $editor_object = new EditPage($article);
        $content_new = new wikitextcontent($content);
        $doEditStatus = $editor_object->mArticle->doEditContent($content_new, $editor_object->summary, 97, false, null, $editor_object->contentFormat);

        if (!$doEditStatus->isOK()) {
            $errors = $doEditStatus->getErrorsArray();
            throw new \Exception('error-newpage');
        }

        return $local_url;
    }

    private function createTitleObjectNewPage($new_page_url) {

        if (null === Title::newFromText($new_page_url)) {
            throw new \Exception('error-newpage');
        }

        $title = Title::newFromText($new_page_url);

        if ($title->exists()) {
            throw new \Exception('error-newpage');
        }

        return $title;
    }

    protected function handleExceptions(Exception $exception_error) {

        $viewer = $this->viewer;
        $error_identifier = $exception_error->getMessage();
        $error_message = $this->constructErrorMessage($exception_error, $error_identifier);
        $this->error_identifier = $error_identifier; 

        if ($error_identifier === 'error-nopermission') {
            return $viewer->showSimpleErrorMessage($error_message);
        }
        
        if($error_identifier === 'error-fewuploads'){
            return $viewer->showFewUploadsError($error_message);
        }

        return $this->getDefaultPage($error_message);
    }

    protected function constructErrorMessage(Exception $exception_error, $error_identifier) {

        global $wgShowExceptionDetails;

        if ($wgShowExceptionDetails === true) {
            $error_file = $exception_error->getFile();
            $error_line = $exception_error->getLine();
            $trace = $this->formatTrace($exception_error->getTrace());
            $error_message = $this->msg($error_identifier) . ' ' . $error_file . ' ' . $error_line . '<br><br>' . $trace;
        }
        else {
            $error_message = $this->msg($error_identifier);
        }
        
        return $error_message;
    }
    
    private function formatTrace(array $trace){
        $trace_text = '';
        foreach($trace as $entry){
            $file = isset($entry['file']) ? $entry['file'] : '';
            $line = isset($entry['line']) ? $entry['line'] : '';
            $entry_line = $file . ' ' . $line . '<br>'; 
            $trace_text .= $entry_line;
        }
        
        return $trace_text; 
    }
    
    public function getErrorIdentifier(){
        return $this->error_identifier; 
    }
    
    /**
     * Get the default page for this special page
     */
    abstract protected function getDefaultPage($error_message);

    /**
     * Return viewer object for the special page
     * 
     * @return ManuscriptDeskBaseViewer object
     */
    abstract protected function setViewer();

    /**
     * Return wrapper object for the special page
     * 
     * @return ManuscriptDeskBaseWrapper object
     */
    abstract protected function setWrapper();

    /**
     * Return request processor object for the special page
     * 
     * @return ManuscriptDeskBaseRequestProcessor object
     */
    abstract protected function setRequestProcessor();
}
