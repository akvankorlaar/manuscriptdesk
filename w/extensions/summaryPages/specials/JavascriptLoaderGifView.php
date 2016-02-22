<?php

trait JavascriptLoaderGifView{

    protected function addJavascriptLoaderGif() {

        $html = "<h3 id='summarypage-loaderdiv' style='display: none;'>Loading";
        $html .= "<span id='summarypage-loaderspan'></span>";
        $html .= "</h3>";

        return $html;
    }
      
}