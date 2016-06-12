<?php

/**
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
* 
* 04 sept 2015: Small layout changes, and some tags removed @Arent van Korlaar 
* 112 juli 2016: Changed zoomviewer to javascript zoomviewer
*
*/

$lang = 'en';

define( 'MEDIAWIKI', '' );

require '../../NewManuscript.i18n.php';

$useJSMsg     = $messages[ $lang ][ 'to-use-brainmaps' ];
$clickHereMsg = $messages[ $lang ][ 'click-here' ];
$insteadMsg   = $messages[ $lang ][ 'instead' ];
$viewerTitle  = $messages[ $lang ][ 'zoomify-viewer' ];
$errorMsg	  = $messages[ $lang ][ 'error' ];

$requiredGetVars = array(
  'image'    => 'imageFilePath',
  'lang'     => 'lang',
  'sitename' => 'siteName',
  );

foreach ($requiredGetVars as $getVar => $varName){
  if(isset($_GET[$getVar]) === true){
    $$varName = $_GET[ $getVar ];
    
  }else{
    $errorMsg = sprintf( $errorMsg, $getVar );
    throw new Exception( $errorMsg );    
  } 
}
    
?>
<head>
<title>[ <?php echo $siteName; ?> - <?php echo $viewerTitle; ?> ]</title>
<link rel=stylesheet href="zoomifyviewer.css" type="text/css" media=screen>
<script type="text/javascript" src="ZoomifyImageViewerExpress-min.js"></script>
<script type="text/javascript"> Z.showImage("zoomviewerframe","<?php echo $imageFilePath; ?>","zSkinPath=/zoomviewerskins&zNavigatorVisible=0");</script>
<body id="main_body">
 <div id="zoomviewerframe"></div>
<p><?php echo $useJSMsg ?> <a href="../ajax-tiledviewer/ajax-tiledviewer.php?&image=<?php echo $imageFilePath; ?>&lang=<?php echo $lang; ?>&sitename=<?php echo str_replace( ' ', '%20', $siteName ); ?>"><?php echo $clickHereMsg; ?></a> <?php echo $insteadMsg; ?>.</p>
</body>
</html>    