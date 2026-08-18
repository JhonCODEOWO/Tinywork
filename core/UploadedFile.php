<?php

namespace Core;

use finfo;

class UploadedFile {
    public function __construct(
        private readonly string $name,
        private readonly string $tmp_path,
        private readonly int $error,
        private readonly string $clientMimeType,
    ){

    }

    public function getError() : int{
        return $this->error;
    }

    public function getTmpName() : string{
        return $this->tmp_path;
    }

    public function getImageSize(): ?array
    {
        return getimagesize($this->tmp_path) ?: null;
    }

    public function getSize(): ?int
    {
        return filesize($this->tmp_path) ?: null;
    }

    public function getMimeType(){
        static $finfo;
        $finfo ??= new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($this->tmp_path);
    }

    public function getExtension(){
        return strtolower(pathinfo($this->getTmpName(), PATHINFO_EXTENSION));
    }

}