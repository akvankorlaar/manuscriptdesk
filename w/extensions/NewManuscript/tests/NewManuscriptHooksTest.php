<?php

/**
 * This file is part of the NewManuscript extension
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
 */

class NewManuscriptHooksTest extends MediaWikiTestCase {

    private $t;
    private $context; 

//    protected function setUp() {        
//        $stub = $this->getMockBuilder('NewManuscriptWrapper')
//                     ->setConstructorArgs(array(null, new AlphabetNumbersWrapper(), new SignatureWrapper()))
//                     ->getMock();
//        
//        $this->t = new NewManuscriptHooks($stub);
//        $this->context = new RequestContext;
//        parent::setUp();
//        return;
//    }
//
//    protected function tearDown() {
//        unset($this->t);
//        unset($this->context);
//        parent::tearDown();
//        return;
//    }
//    
//    public function testonBeforePageDisplay(){
//        $this->setTitle('NewManuscript', NS_SPECIAL, 'Special:NewManuscript');       
//        $skin = new SkinCbpTranscriptionEnhanced();
//        $out = $this->context->getOutput();
//        
//        $this->t->onBeforePageDisplay($out, $skin);       
//        $modules = $out->mModuleStyles; 
//        $result = strpos('ext.manuscriptdeskbasecss', $modules[0]); 
//        $this->assertEquals(is_int($result), true);                     
//        return; 
//    }
//
//    public function testonParserAfterTidy() {
//        $sample_text1 = '</span></span> &lt;/add&gt;';
//        $result_text1 = '</span></span><span class="tei-add"> </span>';
//
//        $sample_text2 = '</span></span> &lt;/del&gt;';
//        $result_text2 = '</span></span><span class="tei-del"> </span>';
//
//        $sample_text3 = '</span></span> &lt;/hi&gt;';
//        $result_text3 = '</span></span><span class="tei-hi superscript"> </span>';
//
//        $parser = $this->getMockBuilder('Parser')
//            ->getMock();
//
//        $this->t->onParserAfterTidy($parser, $sample_text1);
//        $this->assertEquals($sample_text1, $result_text1);
//
//        $this->t->onParserAfterTidy($parser, $sample_text2);
//        $this->assertEquals($sample_text2, $result_text2);
//
//        $this->t->onParserAfterTidy($parser, $sample_text3);
//        $this->assertEquals($sample_text3, $result_text3);
//        return;
//    }
//    
//    private function setRequest(array $data) {
//        $faux_request = new FauxRequest($data, true);
//        $this->context->setRequest($faux_request);
//        return; 
//    }
//    
//    private function setTitle($page_title, $namespace = null, $mPrefixedText = null){
//        $faux_title = Title::newFromText($page_title, $namespace);
//        $faux_title->getPrefixedUrl();
//        $faux_title->mPrefixedText = $mPrefixedText;     
//        $this->context->setTitle($faux_title);
//        return;
//    }

}
