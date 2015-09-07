<?php 

$wgExtensionAssetsPath = $IP . '/extensions/';

//these files are autoloaded to enable the extensions
require_once( $wgExtensionAssetsPath . 'JBTEIToolbar/JBTEIToolbar.php' );
require_once( $wgExtensionAssetsPath . 'TEITags/TEITags.php' );
require_once( $wgExtensionAssetsPath . 'collate/collate.php');
require_once( $wgExtensionAssetsPath . 'newManuscript/newManuscript.php');
require_once( $wgExtensionAssetsPath . 'summaryPages/summaryPages.php');
require_once( $wgExtensionAssetsPath . 'stylometricAnalysis/stylometricAnalysis.php');

$wgArticlePath = "/md/$1";
$wgUsePathInfo = true;        # Enable use of pretty URLs
$wgArticleUrl = "/md/";

# Notes from Transcribe Bentham Developers: BP Enabled by default. We need to switch this off because the bentham modern template is not HTML 5
# HTML Tidy will complain that script tags do not contain a 'type' attribute Html->inlineScript()
$wgHtml5 					= false; 

$wgExternalLinkTarget  = '_blank'; 
$wgShowExceptionDetails = true;

#Enables use of WikiEditor by default but still allow users to disable it in preferences
$wgDefaultUserOptions['usebetatoolbar'] = 1;

//skin settings. The skin used is largely based on the 'cbptranscriptionenhanced' skin, with slight modifications 
$wgValidSkinNames[ 'cbptranscriptionenhanced' ] = "CbpTranscriptionEnhanced";
$wgDefaultSkin = 'cbptranscriptionenhanced';
$wgLocalStylePath = $IP . '/skins/';

require_once( $wgLocalStylePath . 'CbpTranscriptionEnhanced/CbpTranscriptionEnhanced.php' );

//define new namespaces for manuscripts and collations
define("NS_MANUSCRIPTS",3100);
define("NS_MANUSCRIPTS_TALK",3101);

define("NS_COLLATIONS",3200);
define("NS_COLLATIONS_TALK",3201);

$wgExtraNamespaces[NS_MANUSCRIPTS]      = 'Manuscripts';
$wgExtraNamespaces[NS_MANUSCRIPTS_TALK] = 'Manuscripts_talk';
//permission "editmanuscripts" required to edit the Manuscripts namespace
$wgNamespaceProtection[NS_MANUSCRIPTS]  = array( 'editmanuscripts'); 

$wgExtraNamespaces[NS_COLLATIONS]      = 'Collations';
$wgExtraNamespaces[NS_COLLATIONS_TALK] = 'Collations_talk';
//permission "editmanuscripts" required to edit the Manuscripts namespace
$wgNamespaceProtection[NS_COLLATIONS]  = array( 'editcollations');

//set default searching
$wgNamespacesToBeSearchedDefault = array(
  NS_MAIN             => true,
  NS_TALK             => false,
  NS_USER             => false,
  NS_USER_TALK        => false,
  NS_PROJECT          => false,
  NS_PROJECT_TALK     => false,
  NS_IMAGE            => false,
  NS_IMAGE_TALK       => false,
  NS_MEDIAWIKI        => false,
  NS_MEDIAWIKI_TALK   => false,
  NS_TEMPLATE         => false,
  NS_TEMPLATE_TALK    => false,
  NS_HELP             => false,
  NS_HELP_TALK        => false,
  NS_CATEGORY         => false,
  NS_CATEGORY_TALK    => false,
  NS_MANUSCRIPTS      => true,
  NS_MANUSCRIPTS_TALK => false,
  NS_COLLATIONS       => true,
  NS_COLLATIONS_TALK  => false,
);

//user permissions

//first, disable many of the default permissions for anonymous and registered normal users
$wgGroupPermissions['*']['edittype'] = false;
$wgGroupPermissions['*']['edithelp'] = false;
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['createtalk'] = false;
$wgGroupPermissions['*']['editmyusercss'] = false;
$wgGroupPermissions['*']['editmyuserjs'] = false;

$wgGroupPermissions['user']['edit'] = false;
$wgGroupPermissions['user']['createpage'] = false;
$wgGroupPermissions['user']['createtalk'] = false;
$wgGroupPermissions['user']['editmyusercss'] = false;
$wgGroupPermissions['user']['editmyuserjs'] = false;
$wgGroupPermissions['user']['editmyuserjs'] = false;
$wgGroupPermissions['user']['minoredit'] = false;
$wgGroupPermissions['user']['movefile'] = false;
$wgGroupPermissions['user']['move'] = false;
$wgGroupPermissions['user']['move-subpages'] = false;
$wgGroupPermissions['user']['move-rootuserpages'] = false;
$wgGroupPermissions['user']['reupload-shared'] = false;
$wgGroupPermissions['user']['reupload'] = false;
$wgGroupPermissions['user']['purge'] = false;
$wgGroupPermissions['user']['upload'] = false;
$wgGroupPermissions['user']['writeapi'] = false;

$wgGroupPermissions['autoconfirmed']['editsemiprotected'] = false;

//then, make a new group and give it most of the permissions first disabled. Users can then be manually added by a sysop to this group once they have created an account
$wgGroupPermissions['ManuscriptEditors']['edit'] = true;
$wgGroupPermissions['ManuscriptEditors']['createpage'] = true;
$wgGroupPermissions['ManuscriptEditors']['createtalk'] = true;
$wgGroupPermissions['ManuscriptEditors']['editmyusercss'] = true;
$wgGroupPermissions['ManuscriptEditors']['editmyuserjs'] = true;
$wgGroupPermissions['ManuscriptEditors']['editmyuserjs'] = true;
$wgGroupPermissions['ManuscriptEditors']['minoredit'] = true;
//important note: although ManuscriptEditors are given permission to move normal wiki pages, manuscripts and collation pages may not be moved by any user 
//(an error will appear when you try to do this)
$wgGroupPermissions['ManuscriptEditors']['reupload-shared'] = true;
$wgGroupPermissions['ManuscriptEditors']['reupload'] = true;
$wgGroupPermissions['ManuscriptEditors']['purge'] = true;
$wgGroupPermissions['ManuscriptEditors']['upload'] = true;
$wgGroupPermissions['ManuscriptEditors']['writeapi'] = true;
$wgGroupPermissions['ManuscriptEditors']['delete'] = true;
$wgGroupPermissions['ManuscriptEditors']['editmanuscripts'] = true;  
$wgGroupPermissions['ManuscriptEditors']['editcollations'] = true;  

//set additional permissions for sysops, and ensure that sysops have the same base permissions as ManuscriptEditors 
$wgGroupPermissions[ 'sysop' ][ 'edittype' ] = true;
$wgGroupPermissions[ 'sysop' ][ 'edithelp' ] = true;

$wgGroupPermissions['sysop']['edit'] = true;
$wgGroupPermissions['sysop']['createpage'] = true;
$wgGroupPermissions['sysop']['createtalk'] = true;
$wgGroupPermissions['sysop']['editmyusercss'] = true;
$wgGroupPermissions['sysop']['editmyuserjs'] = true;
$wgGroupPermissions['sysop']['editmyuserjs'] = true;
$wgGroupPermissions['sysop']['minoredit'] = true;
$wgGroupPermissions['sysop']['reupload-shared'] = true;
$wgGroupPermissions['sysop']['reupload'] = true;
$wgGroupPermissions['sysop']['purge'] = true;
$wgGroupPermissions['sysop']['upload'] = true;
$wgGroupPermissions['sysop']['writeapi'] = true;
$wgGroupPermissions['sysop']['delete'] = true;
$wgGroupPermissions['sysop']['editmanuscripts'] = true;  
$wgGroupPermissions['sysop']['editcollations'] = true;

$wgNamespaceProtection[ NS_HELP ] 	 = array( 'edithelp' );
$wgNamespacesWithSubpages[ NS_HELP ] = true;

//set a hook to change the sidebar for users that are not in the ManuscriptEditors group
$wgHooks['SkinBuildSidebar'][] = 'onSkinBuildSidebar';

  /**
   * This function removes pages from the navigation sidebar if the user does not have the correct permissions to use these pages
   */
  function onSkinBuildSidebar(Skin $skin, &$bar){
    
    global $wgUser; 
    
    if(in_array('ManuscriptEditors',$wgUser->getGroups()) || in_array('sysop',$wgUser->getGroups())){
      return true; 
    }
    
    //these elements correspond to 'New Manuscript', 'Collate Manuscripts' , 'Stylometric Analysis' and 'My User Page'
    unset($bar['navigation'][1]);
    unset($bar['navigation'][2]);
    unset($bar['navigation'][3]);
    unset($bar['navigation'][4]);
        
    return true;
  }
  
  //set a hook to change the link to the user page (link on the top bar when clicking on the user name)
  $wgHooks['PersonalUrls'][] = 'onPersonalUrls';

  /**
   * 
   * @param array $personal_urls
   * @param Title $title
   * @param SkinTemplate $skin
   */
  function onPersonalUrls(array &$personal_urls, Title $title, SkinTemplate $skin){
    
    global $wgArticleUrl, $wgUser; 
    
    $wg_user = $wgUser; 
    
    //do not display the link to Special:UserPage if the user is not in the ManuscriptEditors group
    if(!in_array('ManuscriptEditors',$wg_user->getGroups())){
      unset($personal_urls['userpage']);
      
      return true; 
      
    //if the current user is in the ManuscriptEditors group, change the link  
    }else{
    
    $article_url = $wgArticleUrl; 
    
    $personal_urls['userpage']['href'] = $article_url . 'Special:UserPage';
    
    return true;
    }
  }

$wgAllowUserJs = false;

//do not allow users to change the preferred language
$wgHiddenPrefs[] = 'language';
//do not allow users to change the preferred skin
$wgHiddenPrefs[] = 'skin'; 
//do not allow users to disable the toolbar 
$wgHiddenPrefs[] = 'showtoolbar'; 
//do not allow users to use the experimental feature 'live preview'
$wgHiddenPrefs[] = 'uselivepreview'; 
//do not allow users to change the image size on description pages or thumbnail size
$wgHiddenPrefs[] = 'imagesize'; 
$wgHiddenPrefs[] = 'thumbsize'; 
//do not allow users to change the edit box size
$wgHiddenPrefs[] = 'rows'; 
$wgHiddenPrefs[] = 'cols'; 

##############################################################
#PERSONAL SETTINGS
##############################################################

//website root. This is used to locate the zoomImages and initialUpload directories. The full path to the website root must be specified here. 
$wgWebsiteRoot = 'path/to/website/root';

//Primary disk. Disk on which the images and the website is stored.  
$wgPrimaryDisk = 'main disk here';

//the following arrays are global configuration settings that are used within the extensions 'collate' and 'newManuscript'
$wgCollationOptions = array(
  'collatex_url' => 'localhost:7369/collate', //url of the collatex server
  'collatex_headers' => array ("Content-type: application/json; charset=UTF-8;",
		"Accept: application/json"), //headers that are sent to collatex
  'wgmin_collation_pages' => 2, //the minimum number of single manuscript pages that users are allowed to collate
  'wgmax_collation_pages' => 5, //the maximum number of single manuscript pages that users are allowed to collate
  'tempcollate_hours_before_delete'  => 2, //hours before entries are deleted from tempcollate. 
);

$wgNewManuscriptOptions = array( 
  'allowed_file_extensions' => array('jpg', 'jpeg'), //allowed file extensions 
  'max_manuscripts' => 300, //maximum allowed manuscript pages per user
  'maximum_pages_per_collection' => 50, //maximum allowed pages for a collection
  'max_upload_size' => 8388608, //maximum upload size in bytes (8 mb --> 8*1024*1024)
  'original_images_dir' =>'initialUpload', //directory of the original images
  'zoomimages_root_dir' => 'zoomImages',//directory of the zoomimages
  'perl_path' => 'perl', //alternative: /usr/bin/perl'.. for unix?  
  'slicer_path' => '/w/extensions/newManuscript/specials/slicer.pl', //path to the slicer
  'max_on_page' => 10, //maximum entries shown per page on Special:AllManuscripts, Special:AllCollations and Special:UserPpage
  'max_recent' => 30, //maximum entries shown on Special:RecentManuscriptPages
  'manuscripts_namespace' => 'Manuscripts:', //url namespace for manuscripts
  'max_charachters_manuscript' => 5000, //a limitation of 5000 charachters has been set, so that when performing text algorithms on the manuscript pages, the server does not receive too much input
  'url_count_size' => 2, //length of a manuscript pages url once transformed into an array by removing the slashes
);