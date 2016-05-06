<?php

/**
 * This file is part of the collate extension
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
 */
class ManuscriptDeskDeleteWrapperTest extends MediaWikiTestCase {

    private $t;
    private $database_test_instance_creator;

    protected function setUp() {
        parent::setUp();
        $this->database_test_instance_creator = new DatabaseTestInserter();
        $this->database_test_instance_creator->createManuscriptsTest();
        $this->t = new ManuscriptDeskDeleteWrapper(null, new AlphabetNumbersWrapper());
    }

    protected function tearDown() {
        $this->database_test_instance_creator->destroyManuscriptsTest();
        unset($this->database_test_instance_creator);
        unset($this->t);
        parent::tearDown();
    }

    public function testgetManuscriptsLowercaseTitle() {
        $result = $this->t->getManuscriptsLowercaseTitle('test/url');
        $this->assertEquals($result, 'test');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage error-database
     */
    public function testgetManuscriptsLowercaseTitleException() {
        $this->t->getManuscriptsLowercaseTitle('some/random/url/just/for/testing/purposes');
    }

}
