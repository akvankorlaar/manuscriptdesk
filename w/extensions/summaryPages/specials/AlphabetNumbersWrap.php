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

trait AlphabetNumbersWrap{

    public function getAlphabetNumbersData($alphabetnumbers_context = '') {

        $dbr = wfGetDB(DB_SLAVE);
        $number_array = array();

        $res = $dbr->select(
            'alphabetnumbers', array(
          'a',
          'b',
          'c',
          'd',
          'e',
          'f',
          'g',
          'h',
          'i',
          'j',
          'k',
          'l',
          'm',
          'n',
          'o',
          'p',
          'q',
          'r',
          's',
          't',
          'u',
          'v',
          'w',
          'x',
          'y',
          'z',
          'zero',
          'one',
          'two',
          'three',
          'four',
          'five',
          'six',
          'seven',
          'eight',
          'nine',
            ), array(
          'alphabetnumbers_context = ' . $dbr->addQuotes($alphabetnumbers_context), //contitions
            ), __METHOD__
        );

        //there should only be one result
        if ($res->numRows() === 1) {
            $s = $res->fetchObject();

            $number_array = array(
              $s->a,
              $s->b,
              $s->c,
              $s->d,
              $s->e,
              $s->f,
              $s->g,
              $s->h,
              $s->i,
              $s->j,
              $s->k,
              $s->l,
              $s->m,
              $s->n,
              $s->o,
              $s->p,
              $s->q,
              $s->r,
              $s->s,
              $s->t,
              $s->u,
              $s->v,
              $s->w,
              $s->x,
              $s->y,
              $s->z,
              $s->zero,
              $s->one,
              $s->two,
              $s->three,
              $s->four,
              $s->five,
              $s->six,
              $s->seven,
              $s->eight,
              $s->nine,
            );
        }

        return $number_array;
    }

}


