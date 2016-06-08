<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$lang = 'en';

define( 'MEDIAWIKI', '' );

require '../../NewManuscript.i18n.php';

$useJSMsg     = $messages[ $lang ][ 'to-use-javascript' ];
$clickHereMsg = $messages[ $lang ][ 'click-here' ];
$insteadMsg   = $messages[ $lang ][ 'instead' ];
$viewerTitle  = $messages[ $lang ][ 'flash-viewer' ];
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


$imageFilePath = str_replace('?','%3F',$imageFilePath);

?>

<head>
    <script type="text/javascript" src="ZoomifyImageViewer-min.js"></script> 
    <style type="text/css"> #myContainer { width: 900px; height: 550px } </style> 
    <script type="text/javascript"> Z.showImage("myContainer", <?php echo $imageFilePath; ?>); </script>
</head>

<body>
    <div id="myContainer"></div>
</body>
