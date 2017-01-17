<?php

namespace Linkman\Http\Formatter\Domain;

use Linkman\Http\Formatter\AbstractFormatter;

class AlbumFormatter extends AbstractFormatter
{
    public function format($album) : array
    {
        return [
            'href' => $this->getHref($album),
            'id' => $album->getId(),
            'title' => $album->getTitle(),

            'createdBy' => $album->getCreatedBy() ? [
                'href' => '/users/' . $album->getCreatedBy()->getId(),
            ] : [],

            'contents' => [
                'href' => $this->getHref($album).'/contents',
                'count' => count($album->getContents())
            ]
        ];
    }

    public function getBaseUrl()
    {
        return $this->apiUrl.'/albums';
    }
}
