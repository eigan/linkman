<?php

namespace Linkman\Http\Formatter\Domain;

use Linkman\Http\Formatter\AbstractFormatter;

class FileFormatter extends AbstractFormatter
{
    public function getBaseUrl()
    {
        return $this->apiUrl.'/files';
    }

    public function format($file) : array
    {
        return [
            'id' => $file->getId(),
            'mount' => (new MountFormatter($this->apiUrl))->format($file->getMount(), $this->embedMode('mount')),

            'path' => $file->getPath(),
            'directoryPath' => $file->getDirectoryPath()
        ];
    }
}
