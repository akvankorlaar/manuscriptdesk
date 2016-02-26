<?php

class UserPageWrapper extends ManuscriptDeskBaseWrapper {

    /**
     * This function retrieves data from the 'manuscripts' table
     */
    private function retrieveUserPageManuscriptPages() {

//        
//         global $wgNewManuscriptOptions;
//        $this->max_on_page = $wgNewManuscriptOptions['max_on_page'];
        
        
        $user_name = $this->user_name;
        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();
        $next_offset = null;

        $res = $dbr->select(
            'manuscripts', //from
            array(
          'manuscripts_title', //values
          'manuscripts_url',
          'manuscripts_date',
          'manuscripts_lowercase_title',
            ), array(
          'manuscripts_user = ' . $dbr->addQuotes($user_name),
          //only select manuscript pages that do not have a collection
          'manuscripts_collection = ' . $dbr->addQuotes('none'),
            ), __METHOD__, array(
          'ORDER BY' => 'manuscripts_lowercase_title',
          'LIMIT' => $this->max_on_page + 1,
          'OFFSET' => $this->offset,
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //add titles to the title array as long as it is not bigger than max_on_page
                if (count($title_array) < $this->max_on_page) {

                    $title_array[] = array(
                      'manuscripts_title' => $s->manuscripts_title,
                      'manuscripts_url' => $s->manuscripts_url,
                      'manuscripts_date' => $s->manuscripts_date,
                    );

                    //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
                }
                else {
                    $this->next_page_possible = true;
                    $next_offset = ($this->offset) + ($this->max_on_page);
                    break;
                }
            }
        }

        return array($title_array, $next_offset, $this->next_page_possible);
    }

    /**
     * This function retrieves data from the 'collations' table
     * 
     * @param type $dbr
     * @param type $conds
     * @param type $titles_array
     */
    private function retrieveUserPageCollations() {

        $user_name = $this->user_name;
        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();
        $next_offset = null;

        //Database query
        $res = $dbr->select(
            'collations', //from
            array(
          'collations_url', //values
          'collations_date',
          'collations_main_title',
          'collations_main_title_lowercase'
            ), array(
          'collations_user = ' . $dbr->addQuotes($user_name),
            ), __METHOD__, array(
          'ORDER BY' => 'collations_main_title_lowercase',
          'LIMIT' => $this->max_on_page + 1,
          'OFFSET' => $this->offset,
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //add titles to the title array as long as it is not bigger than max_on_page
                if (count($title_array) < $this->max_on_page) {

                    $title_array[] = array(
                      'collations_url' => $s->collations_url,
                      'collations_date' => $s->collations_date,
                      'collations_main_title' => $s->collations_main_title,
                    );

                    //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
                }
                else {
                    $this->next_page_possible = true;
                    $next_offset = ($this->offset) + ($this->max_on_page);
                    break;
                }
            }
        }

        return array($title_array, $next_offset, $this->next_page_possible);
    }

    /**
     * This function retrieves data of manuscripts contained in collections from the 'manuscripts' table
     * 
     * @return type
     */
    private function retrieveUserPageCollections() {

        $user_name = $this->user_name;
        $dbr = wfGetDB(DB_SLAVE);
        $title_array = array();
        $next_offset = null;

        //Database query
        $res = $dbr->select(
            'collections', //from
            array(
          'collections_title',
          'collections_date',
            ), array(
          'collections_user = ' . $dbr->addQuotes($user_name),
          'collections_title != ' . $dbr->addQuotes(""),
          'collections_title != ' . $dbr->addQuotes("none"),
            ), __METHOD__, array(
          'ORDER BY' => 'collections_title',
          'LIMIT' => $this->max_on_page + 1,
          'OFFSET' => $this->offset,
            )
        );

        if ($res->numRows() > 0) {
            //while there are still titles in this query
            while ($s = $res->fetchObject()) {

                //add titles to the title array as long as it is not bigger than max_on_page
                if (count($title_array) < $this->max_on_page) {

                    $title_array[] = array(
                      'collections_title' => $s->collections_title,
                      'collections_date' => $s->collections_date,
                    );

                    //if there is still a title to add (max_on_page+1 has been reached), it is possible to go to the next page
                }
                else {
                    $this->next_page_possible = true;
                    $next_offset = ($this->offset) + ($this->max_on_page);
                    break;
                }
            }
        }

        return array($title_array, $next_offset, $this->next_page_possible);
    }

}
