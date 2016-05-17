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
class UserPageDefaultViewer extends ManuscriptDeskBaseViewer {

    use HTMLJavascriptLoaderDots,
        HTMLUserPageMenuBar;

    /**
     * This function shows the default page if no request was posted 
     */
    public function showDefaultPage($error_message = '', $user_name, $user_is_a_sysop) {

        $out = $this->out;

        $out->setPageTitle($out->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $edit_token = $out->getUser()->getEditToken();
        $html .= $this->getHTMLUserPageMenuBar($out, $edit_token);
        $html .= $this->getHTMLJavascriptLoaderDots();

        $html .= "<div class='javascripthide'>";

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }


        if ($user_is_a_sysop) {
            $html .= $this->getHTMLMessageSpaceLeftOnDisk($out);
        }

        $html .= "</div>";

        return $out->addHTML($html);
    }

    private function getHTMLMessageSpaceLeftOnDisk(OutputPage $out) {

        global $wgPrimaryDisk; 
        
        $free_disk_space_bytes = disk_free_space($wgPrimaryDisk);
        $free_disk_space_mb = round($free_disk_space_bytes / 1048576);
        $free_disk_space_gb = round($free_disk_space_mb / 1024);

        $admin_message1 = $out->msg('userpage-admin1');
        $admin_message2 = $out->msg('userpage-admin2');
        $admin_message3 = $out->msg('userpage-admin3');
        $admin_message4 = $out->msg('userpage-admin4');

        return "<p>" . $admin_message1 . ' ' . $free_disk_space_bytes . ' ' . $admin_message2 . ' ' . $free_disk_space_mb . ' ' . $admin_message3 . ' ' . $free_disk_space_gb . ' ' . $admin_message4 . ".</p>";
    }

}
