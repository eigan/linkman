<?php

namespace Linkman\Plugin\Hook;

use Linkman\Domain\File;
use Linkman\Domain\FileContent;
use Linkman\Domain\Photo;

class Exif
{
    public function __construct($fileservice)
    {
        $this->fileservice = $fileservice;
    }

    public function tag(FileContent $photo, File $file)
    {
        if ($photo instanceof Photo == false) {
            return;
        }

        $exifData = @exif_read_data($this->fileservice->realpath($file));

        if (is_array($exifData) == false) {
            return;
        }

        $photo->update($exifData);
    }
}
