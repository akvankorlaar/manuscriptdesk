<?php

/**
 * This file is part of the newManuscript extension
 * Copyright (C) 2015 Arent van Korlaar
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
 * 
 * php -d xdebug.profiler_enable=On phpunit.php C:\xampp\htdocs\mediawikinew\w\extensions\SummaryPages
 */
class SignatureChangeWrapperTest extends MediaWikiTestCase {

    private $t;
    private $test_wrapper;

    protected function setUp() {
        parent::setUp();
        $this->test_wrapper = new DatabaseTestInserter();
        $this->test_wrapper->createManuscriptsTest();
        $this->t = new ImageSignatureWrapper();
    }

    protected function tearDown() {
        $this->test_wrapper->destroyManuscriptsTest();
        unset($this->test_wrapper);
        unset($this->t);
        parent::tearDown();
    }

    public function testgetManuscriptSignature() {
        $signature = $this->t->getManuscriptSignature('test/url');
        $this->assertEquals($signature, 'private');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-database
     */
    public function testsetManuscriptSignatureException() {
        $this->t->setManuscriptSignature('test/url', 'exception');
    }

    public function testsetManuscriptSignature() {
        $result = $this->t->setManuscriptSignature('test/url', 'public');
        $this->assertEquals($result, true);
    }

}
