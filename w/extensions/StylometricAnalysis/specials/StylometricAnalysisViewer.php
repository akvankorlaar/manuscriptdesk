<?php

class StylometricAnalysisViewer{
  	
  private $out; 
  private $max_length;
  private $article_url; 
	
  public function __construct(Outputpage $out){
	  
	global $wgArticleUrl; 
	  
	$this->out = $out;
	$this->article_url = $wgArticleUrl;
	$this->max_length = 50;
  }
  	
 /**
  * This function adds html used for the stylometricanalysis loader
  */
  private function addStylometricAnalysisLoader(){
    
    //shows after submit has been clicked
    $html  = "<div id='stylometricanalysis-loaderdiv'>";
    $html .= "<img id='stylometricanalysis-loadergif' src='/w/extensions/collate/specials/assets/362.gif' style='width: 64px; height: 64px;"
        . " position: relative; left: 50%;'>"; 
    $html .= "</div>";
    
    return $html; 
  }

   
  /**
   * This function constructs the HTML for the default page
   */
  public function showForm1($user_collections){
      
    $out = $this->getOutput();   
    $article_url = $this->article_url; 
    
    $out->setPageTitle($this->msg('stylometricanalysis-welcome'));
    
    $about_message = $this->msg('stylometricanalysis-about');
    $version_message = $this->msg('stylometricanalysis-version');  
    $software_message = $this->msg('stylometricanalysis-software');
    $lastedit_message = $this->msg('stylometricanalysis-lastedit');
    
    $html  = "<table id='stylometricanalysis-infobox'>";
    $html .= "<tr><th>$about_message</th></tr>";
    $html .= "<tr><td>$version_message</td></tr>";
    $html .= "<tr><td>$software_message <a href= '' target='_blank'>    </a>.</td></tr>";
    $html .= "<tr><td id='stylometricanalysis-td'><small>$lastedit_message</small></td></tr>";
    $html .= "</table>";
    
    $html .= "<p>" . $this->msg('stylometricanalysis-instruction1') . '</p>';
    
    $html .= "<div id='javascript-error'></div>"; 
            
    //display the error 
    if($this->error_message){     
      $error_message = $this->error_message;  
      $html .= "<div class = 'error'>". $error_message . "</div>";
    }
            
    $html .= "<form id='stylometricanalysis-form' action='" . $article_url . "Special:StylometricAnalysis' method='post'>";    
    $html .= "<h3>" . $this->msg('stylometricanalysis-collectionheader') . "</h3>";
       
    $html .= "<table class='stylometricanalysis-table'>";

    $a = 0;
    $html .= "<tr>";
    
    foreach($user_collections as $collection_name=>$small_url_array){

      if(($a % 4) === 0){  
        $html .= "</tr>";
        $html .= "<tr>";    
      }

      $manuscripts_urls = $small_url_array['manuscripts_url'];
      $manuscripts_urls['collection_name'] = $collection_name; 

      foreach($manuscripts_urls as $index=>&$url){
        $url = htmlspecialchars($url);
      }
      
      //encode the array into json to be able to place it in the checkbox value
      $json_small_url_array = json_encode($manuscripts_urls);       
      $manuscript_pages_within_collection = htmlspecialchars(implode(', ',$small_url_array['manuscripts_title']));   
      $collection_text = $this->msg('stylometricanalysis-contains') . $manuscript_pages_within_collection . '.';

      //add a checkbox for the collection
      $html .="<td>";
      $html .="<input type='checkbox' class='stylometricanalysis-checkbox' name='collection$a' value='$json_small_url_array'>" . htmlspecialchars($collection_name);
      $html .= "<br>";
      $html .= "<span class='stylometricanalysis-span'>" . $collection_text . "</span>"; 
      $html .="</td>";
      $a = ++$a; 
    }

    $html .= "</tr>";
    $html .= "</table>";
  
    $html .= "<br><br>"; 
    
    $submit_hover_message = $this->msg('stylometricanalysis-hover');
    $submit_message = $this->msg('stylometricanalysis-submit');
    
    $html .= "<input type='submit' disabled id='stylometricanalysis-submitbutton' title = $submit_hover_message value=$submit_message>";  
    $html .= "<input type='hidden' name='form1Posted' value='form1Posted'>";
    $html .="</form>";   
    $html .= "<br>";  
    
    $html .= $this->addStylometricAnalysisLoader();
        
    $out->addHTML($html);
    
    return true; 
  }
  
  /**
   * This function constructs and shows the stylometric analysis form
   */
  public function showForm2(){
    
    $article_url = $this->article_url; 
    $collection_array = $this->collection_array;
    $max_length = $this->max_length; 
    $out = $this->out; 
	
	$collection_name_array = array();
    
    foreach($collection_array as $index=>$small_url_array){
      $collection_name_array[] = $small_url_array['collection_name'];
    }
    
    $collections_message = implode(', ',$collection_name_array) . ".";
        
    $out->setPageTitle($this->msg('stylometricanalysis-options'));
    
    $html = "";
    $html .= "<div id='stylometricanalysis-wrap'>";
    $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Go Back'>Go Back</a>";
    $html .= "<br><br>";
    $html .= $this->msg('stylometricanalysis-chosencollections') . $collections_message . "<br>"; 
    $html .= $this->msg('stylometricanalysis-chosencollection2');   
    $html .= "<br><br>";
    
    //display the error 
    if($this->error_message){     
      $error_message = $this->error_message;  
      $html .= "<div class = 'error'>". $error_message . "</div>";
    }
    
    $html .= "</div>";
    
    $html .= $this->addStylometricAnalysisLoader();
    
    $out->addHTML($html);
    
    $descriptor = array();
    
    $descriptor['removenonalpha'] = array(
      'label' => 'Remove non-alpha',
      'class' => 'HTMLCheckField',
      'section' => 'stylometricanalysis-section-import',
    );
    
    $descriptor['lowercase'] = array(
      'label' => 'Lowercase',
      'class' => 'HTMLCheckField',
      'section' => 'stylometricanalysis-section-import',
    );
    
    $descriptor['tokenizer'] = array(
      'label' => 'Tokenizer',
      'class' => 'HTMLSelectField',
      'options' => array( 
        'Whitespace' => 'whitespace',
        'Words' => 'words',
      ),
      'default' => 'whitespace',
      'section' => 'stylometricanalysis-section-preprocess',
    );
     
    $descriptor['minimumsize'] = array(
      'label' => 'Minimum Size',
      'class' => 'HTMLTextField',
      'default' => 0, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['maximumsize'] = array(
      'label' => 'Maximum Size',
      'class' => 'HTMLTextField',
      'default' => 10000, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['segmentsize'] = array(
      'label' => 'Segment Size',
      'class' => 'HTMLTextField',
      'default' => 0, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['stepsize'] = array(
      'label' => 'Step Size',
      'class' => 'HTMLTextField',
      'default' => 0, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-preprocess',
    );
    
    $descriptor['removepronouns'] = array(
      'label' => 'Remove Pronouns',
      'class' => 'HTMLCheckField',
      'section' => 'stylometricanalysis-section-preprocess',
    );
     
        
    //add field for 'remove these items too'
    
    $descriptor['vectorspace'] = array(
      'label' => 'Vector Space',
      'class' => 'HTMLSelectField',
      'options' => array( 
        'tf'        => 'tf',
        'tf_scaled' => 'tf_scaled',
        'tf_std'    => 'tf_std',
        'tf_idf'    => 'tf_idf',
        'bin'       => 'bin'
      ),
      'default' => 'tf',
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['featuretype'] = array(
      'label' => 'Feature Type',
      'class' => 'HTMLSelectField',
      'options' => array( 
        'word'       => 'word',
        'char'       => 'char',
        'char_wb'    => 'char_wb',
      ),
      'default' => 'word',
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['ngramsize'] = array(
      'label' => 'Ngram Size',
      'class' => 'HTMLTextField',
      'default' => 1, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['mfi'] = array(
      'label' => 'MFI',
      'class' => 'HTMLTextField',
      'default' => 100, 
      'size' => 5, //display size
      'maxlength'=> 5, //input size
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['minimumdf'] = array(
      'class' => 'HTMLTextField',
      'label' => 'Minimum DF',
      'default' => 0.00, 
      'size' => 5,
      'maxlength'=> 5,
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['maximumdf'] = array(
      'class' => 'HTMLTextField',
      'label' => 'Maximum DF',
      'default' => 0.90, 
      'size' => 5, 
      'maxlength'=> 5, 
      'section' => 'stylometricanalysis-section-feature',
    );
    
    $descriptor['visualization1'] = array(
      'label' => 'Visualization1',
      'class' => 'HTMLSelectField',
      'options' => array( 
         'Hierarchical Clustering Dendrogram'  => 'dendrogram',
         'PCA Scatterplot' => 'pcascatterplot',
         'TNSE Scatterplot' => 'tnsescatterplot',
         'Distance Matrix Clustering' => 'distancematrix',
         'Hierarchical Clustering' => 'hierarchicalclustering',
         'Variability Based Neighbour Clustering' => 'neighbourclustering',
      ),
      'default' => 'dendrogram',
      'section' => 'stylometricanalysis-section-visualization',
    );
    
    $descriptor['visualization2'] = array(
      'label' => 'Visualization2',
      'class' => 'HTMLSelectField',
      'options' => array( 
         'Hierarchical Clustering Dendrogram'  => 'dendrogram',
         'PCA Scatterplot' => 'pcascatterplot',
         'TNSE Scatterplot' => 'tnsescatterplot',
         'Distance Matrix Clustering' => 'distancematrix',
         'Variability Based Neighbour Clustering' => 'neighbourclustering',
      ),
      'default' => 'dendrogram',
      'section' => 'stylometricanalysis-section-visualization',
    );
    
    $html_form = new HTMLForm($descriptor, $this->getContext());
    $html_form->setSubmitText($this->msg('stylometricanalysis-submit'));
    $html_form->addHiddenField('collection_array', json_encode($collection_array));
    $html_form->addHiddenField('form2Posted', 'form2Posted');
    $html_form->setSubmitCallback(array('SpecialStylometricAnalysis', 'processInput'));  
    $html_form->show();
    
    return true; 
  }
  
  /**
    * Callback function. Makes sure the page is redisplayed in case there was an error. 
    */
  static function processInput($form_data){ 
    return false; 
  }
  
  /**
   * This function shows the output page after the stylometric analysis has completed
   */
  private function showResult($output){
          
    $out = $this->getOutput();
    $article_url = $this->article_url;
    $full_outputpath1 = $this->full_outputpath1;
    $full_outputpath2 = $this->full_outputpath2; 
    $full_linkpath1 = $this->full_linkpath1;
    $full_linkpath2 = $this->full_linkpath2;
    
    $out->setPageTitle($this->msg('stylometricanalysis-output'));
        
    $html = "";
        
    $html .= "<a href='" . $article_url . "Special:StylometricAnalysis' class='link-transparent' title='Perform New Analysis'>Perform New Analysis</a>";

    //save current analysis button
    
    $html .= "<div style='display:block;'>";
    
    $html .= "<div id='visualization-wrap1'>";
    $html .= "<h2>Analysis One </h2>";
    $html .= "<p>Information about the plot</p>";
    $html .= "<img src='" . $full_linkpath1 . "' alt='Visualization1' height='650' width='650'>";  
    $html .= "</div>";
    
    $html .= "<div id='visualization-wrap2'>";
    $html .= "<h2>Analysis Two </h2>";
    $html .= "<p>Information about the plot</p>";
    $html .= "<img src='" . $full_linkpath2 . "' alt='Visualization2' height='650' width='650'>";  
    $html .= "</div>"; 
    
    $html .= "</div>";
    
    $html .= "<div id='visualization-wrap3'>";    
    $html .= "<h2>Analysis Variables</h2><br>";
    $html .= "Remove non-alpha:" . $this->removenonalpha . "<br>";
    $html .= "Lowercase:" . $this->lowercase . "<br>";
    $html .= "Tokenizer:" . $this->tokenizer . "<br>";
    $html .= "Minimum Size:" . $this->minimumsize . "<br>";
    $html .= "Maximum Size:" . $this->maximumsize . "<br>"; 
    $html .= "Segment Size:" . $this->segmentsize . "<br>";
    $html .= "Step Size:" . $this->stepsize . "<br>";
    $html .= "Remove Pronouns:" . $this->removepronouns . "<br>";
    $html .= "Vectorspace:" . $this->vectorspace . "<br>";
    $html .= "Featuretype:" . $this->featuretype . "<br>";
    $html .= "Ngram Size:" . $this->ngramsize . "<br>";
    $html .= "MFI:" . $this->mfi . "<br>";
    $html .= "Minimum DF:" . $this->minimumdf . "<br>";
    $html .= "Maximum DF:" . $this->maximumdf;
    $html .= "</div>";
    
    $html .= "This is the output of Pystyl: $output";
    
    return $out->addHTML($html);
  }	
}

