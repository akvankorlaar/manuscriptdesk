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
class UserPageDefaultViewer extends ManuscriptDeskBaseViewer {
    
    use HTMLJavascriptLoaderGif, HTMLUserPageMenuBar;

    /**
     * This function shows the default page if no request was posted 
     */
    public function showDefaultPage($error_message = '', $user_name, $user_is_a_sysop, $button_name) {

        global $wgArticleUrl;

        $out = $this->out;
        $article_url = $wgArticleUrl;

        $out->setPageTitle($this->msg('userpage-welcome') . ' ' . $user_name);

        $html = "";
        $edit_token = $out->getUser()->getEditToken();
        $html .= $this->getHTMLUserPageMenuBar($edit_token);

        if (!empty($error_message)) {
            $html .= "<br>";
            $html .= "<div class = 'error'>$error_message</div>";
        }

        $html .= $this->getHTMLJavascriptLoaderGif();

        if ($user_is_a_sysop) {
            $html .= $this->getHTMLMessageSpaceLeftOnDisk();
        }

        return $out->addHTML($html);
    }

    private function getHTMLMessageSpaceLeftOnDisk() {

        global $wgPrimaryDisk;
        $pimary_disk = $wgPrimaryDisk;

        $free_disk_space_bytes = disk_free_space($primary_disk);
        $free_disk_space_mb = round($free_disk_space_bytes / 1048576);
        $free_disk_space_gb = round($free_disk_space_mb / 1024);

        $admin_message1 = $this->msg('userpage-admin1');
        $admin_message2 = $this->msg('userpage-admin2');
        $admin_message3 = $this->msg('userpage-admin3');
        $admin_message4 = $this->msg('userpage-admin4');

        return "<p>" . $admin_message1 . ' ' . $free_disk_space_bytes . ' ' . $admin_message2 . ' ' . $free_disk_space_mb . ' ' . $admin_message3 . ' ' . $free_disk_space_gb . ' ' . $admin_message4 . ".</p>";
    }

}
