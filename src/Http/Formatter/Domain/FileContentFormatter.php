<?php

namespace Linkman\Http\Formatter\Domain;

use DateTime;
use Linkman\Domain\FileContent;
use Linkman\Domain\Photo;
use Linkman\Domain\UnknownContentType;
use Linkman\Http\Formatter\AbstractFormatter;

class FileContentFormatter extends AbstractFormatter
{
    /**
     * @param FileContent $fileContent
     */
    public function format($fileContent) : array
    {
        return [
            'href' => $this->getBaseUrl().'/'.$fileContent->getId(),
            'id' => $fileContent->getId(),
            'hash' => $fileContent->getHash(),
            'createdAt' => $fileContent->getCreatedAt()->format(DateTime::ATOM),
            'size' => $fileContent->getSize(),
            'albums' => [
                'href' => $this->getHref($fileContent) . '/albums',
                'count' => count($fileContent->getAlbums())
            ],
            'files' => [
                'href' => $this->getHref($fileContent) . '/files',
                'count' => count($fileContent->getFiles())
            ],
            'tags' => [
                'href' => $this->getHref($fileContent) . '/tags',
                'count' => count($fileContent->getTags())
            ],

            'variants' => [
                'original' => $this->getHref($fileContent) . '/raw?format=original',
                'thumb' => $this->getHref($fileContent) . '/raw?format=thumb',
                'custom' => $this->getHref($fileContent) . '/raw?format=custom',
            ],

            'meta' => $this->getMeta($fileContent),

            'mime' => $fileContent->getFileType(),

            'hidden' => $fileContent->isHidden(),

            'type' => $this->getType($fileContent),
            '$class' => get_class($fileContent)
        ];
    }

    /**
     * Return meta for the file content type, exif for instance
     */
    private function getMeta($fileContent) : array
    {
        switch (get_class($fileContent)) {
            case Photo::class:
                return [
                    'width' => $fileContent->getWidth(),
                    'height' => $fileContent->getHeight(),
                    'make' => $fileContent->getMake(),
                    'model' => $fileContent->getModel(),
                    'software' => $fileContent->getSoftware(),
                    'exposureTime' => $fileContent->getExposureTime(),
                    'fNumber' => $fileContent->getFnumber(),
                    'iso' => $fileContent->getIso(),
                    'width' => $fileContent->getWidth(),
                    'height' => $fileContent->getHeight(),
                ];
            break;
        }

        return [];
    }

    private function getType($fileContent)
    {
        switch (get_class($fileContent)) {
            case Photo::class:
                return 'photo';
            case UnknownContentType::class:
                return 'unknown';
        }
    }

    public function getBaseUrl()
    {
        return $this->apiUrl.'/contents';
    }
}
