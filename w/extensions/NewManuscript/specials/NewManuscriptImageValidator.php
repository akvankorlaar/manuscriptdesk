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
class NewManuscriptImageValidator {

    private $request;
    private $upload_base;

    public function __construct(WebRequest $request) {
        $this->request = $request;
    }

    public function getAndCheckUploadedImageData() {
        $this->checkWhetherFileIsImage();
        $upload_base = $this->getUploadBaseObject();
        $file_name = $this->getFileName($upload_base);
        $extension = $this->getExtension($file_name);
        $temp_path = $this->getTempPath($upload_base);
        $mime_type = $this->getGuessedMimeType($temp_path);

        if ($upload_base::detectScript($temp_path, $mime_type, $extension) === true) {
            throw new \Exception('newmanuscript-error-scripts');
        }

        return array($temp_path, $extension);
    }

    private function checkWhetherFileIsImage() {

        if (!isset($_FILES["wpUploadFile"]["tmp_name"]) || getimagesize($_FILES["wpUploadFile"]["tmp_name"]) === false) {
            throw new \Exception('newmanuscript-error-noimage');
        }

        return;
    }

    private function getUploadBaseObject() {
        global $wgNewManuscriptOptions;
        $max_upload_size = $wgNewManuscriptOptions['max_upload_size'];
        $this->setUploadBase();
        $upload_base = $this->upload_base;

        if (!isset($upload_base)) {
            throw new \Exception('error-request');
        }

        if ($upload_base->getFileSize() > $max_upload_size) {
            throw new \Exception('newmanuscript-error-toolarge');
        }

        return $upload_base;
    }

    private function getFileName(UploadBase $upload_base) {
        $title = $upload_base->getTitle();

        if (!isset($title)) {
            throw new \Exception('error-request');
        }

        return $title->getText();
    }

    private function getExtension($file_name) {
        global $wgNewManuscriptOptions;
        $allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];

        if (pathinfo($file_name, PATHINFO_EXTENSION === null)) {
            throw new \Exception('newmanuscript-error-noextension');
        }

        $extension = pathinfo($file_name, PATHINFO_EXTENSION);

        if ($extension === "") {
            throw new \Exception('newmanuscript-error-noextension');
        }

        if (!in_array($extension, $allowed_file_extensions)) {
            throw new \Exception('newmanuscript-error-fileformat');
        }

        return $extension;
    }

    private function getTempPath(UploadBase $upload_base) {
        $temp_path = $upload_base->getTempPath();

        if ($temp_path === '' || $temp_path === null) {
            throw new \Exception('newmanuscript-error-nofile');
        }

        return $temp_path;
    }

    private function getGuessedMimeType($temp_path) {
        global $wgNewManuscriptOptions;
        $allowed_file_extensions = $wgNewManuscriptOptions['allowed_file_extensions'];

        $magic = MimeMagic::singleton();
        $mime_type = $magic->guessMimeType($temp_path);

        foreach ($allowed_file_extensions as $extension) {
            if (strpos($mime_type, $extension) !== false) {
                return $mime_type;
            }
        }

        throw new \Exception('newmanuscript-error-fileformat');
    }

    public function setUploadBase($object = null) {

        if (isset($this->upload_base)) {
            return;
        }

        return $this->upload_base = isset($object) ? $object : UploadBase::createFromRequest($this->request);
    }

}
