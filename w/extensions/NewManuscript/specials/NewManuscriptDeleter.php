<?php

//
//class NewManuscriptDeleter{
//    
//    private $user_name;
//    private $manuscript_title;
//    private $collection_title;
//    
//public function __construct($user_name, $manuscript_title, $collection_title){
//    $this->user_name = $user_name;
//    $this->manuscript_title = $manuscript_title;
//    $this->collection_title = $collection_title; 
//}
//    
//    
//public function deleteFilesAndDatabaseEntries() {
//        $this->deleteFiles();
//        $wrapper = new NewManuscriptWrapper($this->user_name);
//        $this->deleteDatabaseEntries();
//        $this->subtractAlphabetNumbersTable();
//        return;
//    }
//
//    private function deleteFiles() {
//        $paths = new NewManuscriptPaths($this->user_name, $this->manuscript_title);
//        $this->deleteZoomImageFiles($paths);
//        $this->deleteOriginalImage($paths);
//
//        return;
//    }
//
//    private function deleteDatabaseEntries(NewManuscriptWrapper $wrapper) {
//        $wrapper->deleteFromManuscripts($this->partial_url);
//
//        if (isset($this->collection_title) && $this->collection_title !== 'none') {
//            $wrapper->checkAndDeleteCollectionifNeeded($this->collection_title);
//        }
//        
//        return;
//    }
//
//    private function subtractAlphabetNumbersTable(NewManuscriptWrapper $wrapper) {
//        $main_title_lowercase = $wrapper->getManuscriptsLowercaseTitle($this->partial_url);
//        $alphabetnumbes_context = $wrapper->determineAlphabetNumbersContextFromCollectionTitle($this->collection_title);
//        $wrapper->subtractAlphabetNumbers($main_title_lowercase, $alphabetnumbes_context);
//        return;
//    }
//    
//}
