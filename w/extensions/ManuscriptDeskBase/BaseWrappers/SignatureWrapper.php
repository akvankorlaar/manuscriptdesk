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
class SignatureWrapper {
    
    public function __construct(){
        
    }

    public function getCollationsSignature($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'collations', //from
            array(
          'collations_signature', //values
          'collations_url',
            ), array(
          'collations_url = ' . $dbr->addQuotes($partial_url),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();

        $signature = $s->collations_signature;

        return $signature;
    }

    public function setCollationsSignature($partial_url, $signature) {

        if ($signature !== 'private' && $signature !== 'public') {
            throw new \Exception('error-database');
        }

        $dbw = wfGetDB(DB_MASTER);

        $dbw->update('collations', //select table
            array(//insert values
          'collations_signature' => $signature,
            ), array(//conditions
          'collations_url = ' . $dbw->addQuotes($partial_url),
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('error-database');
        }

        return true;
    }

    public function getManuscriptSignature($url_with_namespace) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_signature', //values
          'manuscripts_url',
            ), array(
          'manuscripts_url = ' . $dbr->addQuotes($url_with_namespace),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();

        $signature = $s->manuscripts_signature;

        return $signature;
    }

    public function setManuscriptSignature($url_with_namespace, $signature) {

        if ($signature !== 'private' && $signature !== 'public') {
            throw new \Exception('error-database');
        }

        $dbw = wfGetDB(DB_MASTER);

        $dbw->update('manuscripts', //select table
            array(//insert values
          'manuscripts_signature' => $signature,
            ), array(//conditions
          'manuscripts_url = ' . $dbw->addQuotes($url_with_namespace),
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('error-database');
        }

        return true;
    }

    public function getStylometricAnalysisSignature($partial_url) {
        $dbr = wfGetDB(DB_SLAVE);

        $res = $dbr->select(
            'stylometricanalysis', //from
            array(
          'stylometricanalysis_signature', //values
          'stylometricanalysis_new_page_url',
            ), array(
          'stylometricanalysis_new_page_url = ' . $dbr->addQuotes($partial_url),
            )
        );

        if ($res->numRows() !== 1) {
            throw new \Exception('error-database');
        }

        $s = $res->fetchObject();

        $signature = $s->stylometricanalysis_signature;

        return $signature;
    }

    public function setStylometricAnalysisSignature($partial_url, $signature) {

        if ($signature !== 'private' && $signature !== 'public') {
            throw new \Exception('error-database');
        }

        $dbw = wfGetDB(DB_MASTER);

        $dbw->update('stylometricanalysis', //select table
            array(//insert values
          'stylometricanalysis_signature' => $signature,
            ), array(//conditions
          'stylometricanalysis_new_page_url = ' . $dbw->addQuotes($partial_url),
            ), __METHOD__
        );

        if (!$dbw->affectedRows()) {
            throw new Exception('error-database');
        }

        return true;
    }

}
