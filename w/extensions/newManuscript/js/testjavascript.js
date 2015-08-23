(function (mw, $){
  
  $( function () {
  
  if ( $( '#nonexistent' ).length ) {
    // This code will only run if there's a matching element
  }

  $("button").click(function(){
        $("p").hide();
   });
        
  }); 
  
  
}(mediaWiki, jQuery));

