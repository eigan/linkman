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

            'path' => $file->getPath(),
            'directoryPath' => $file->getDirectoryPath(),
            'duplicates' => [
                'count' => count($file->getContent()->getFiles()),
                'href' => $this->getBaseUrl() . '?content=' . $file->getContent()->getId()
            ],
            'content' => [
                'id' => $file->getContent()->getId(),
                'href' => $this->apiUrl . '/contents/' . $file->getContent()->getId()
            ],
            'mount' => (new MountFormatter($this->apiUrl))->format($file->getMount(), $this->embedMode('mount')),
        ];
    }
}
