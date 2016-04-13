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
 */

class DatabaseTestInserter {

    private $default_id = 1;
    private $default_value = 'test';
    private $test_url = 'test/url';

    /**
     * This function insert data into the manuscripts table
     */
    public function createManuscriptsTest() {

        $dbw = wfGetDB(DB_MASTER);

        $value = $this->default_value;
        $id = $this->default_id;
        $test_url = $this->test_url;

        $dbw->insert('manuscripts', //select table
            array(//insert values
          'manuscripts_id' => $id,
          'manuscripts_title' => $value,
          'manuscripts_user' => $value,
          'manuscripts_url' => $test_url,
          'manuscripts_date' => $value,
          'manuscripts_lowercase_title' => $value,
          'manuscripts_collection' => $value,
          'manuscripts_lowercase_collection' => $value,
          'manuscripts_datesort' => $value,
            ), __METHOD__, 'IGNORE');
        if (!$dbw->affectedRows()) {
            throw new \Exception('error-testdatabase');
        }

        return;
    }

    public function destroyManuscriptsTest() {

        $dbw = wfGetDB(DB_MASTER);
        $test_url = $this->test_url;

        $dbw->delete(
            'manuscripts', //from
            array(
          'manuscripts_url' => $test_url), //conditions
            __METHOD__);

        if (!$dbw->affectedRows()) {
            throw new \Exception('error-testdatabase');
        }

        return true;
    }

}
