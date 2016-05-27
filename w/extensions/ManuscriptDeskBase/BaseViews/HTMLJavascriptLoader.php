<?php

/**
 * This file is part of the Manuscript Desk (github.com/akvankorlaar/manuscriptdesk)
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
trait HTMLJavascriptLoader {

    /**
     * Render HTML for the javascript loader gif
     * Source of the gif: http://preloaders.net/en/circular
     */
    protected function getHTMLJavascriptLoader() {

        $html = '';
        $html .= "<div class='javascriptloader'>";
        $html .= "<img class='javascriptgif' src='/w/extensions/ManuscriptDeskBase/assets/362.gif' style='width: 64px; height: 64px;"
            . " position: relative; left: 50%;'>";
        $html .= "</div>";

        return $html;
    }

}
