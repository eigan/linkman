<?php

namespace Linkman\Http\Formatter\Domain;

use DateTime;
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
            ],
        ] + $this->getLastFirstContent($album);
    }

    private function getLastFirstContent($album)
    {
        $contents = $album->getContents()->toArray();

        $first = array_reduce($contents, function($first, $content) {
            if($first == null || $content->getCreatedAt() < $first) {
                return $content->getCreatedAt();
            }

            return $first;
        });

        $last = array_reduce($contents, function($last, $content) {
            if($last == null || $content->getCreatedAt() > $last) {
                return $content->getCreatedAt();
            }

            return $last;
        });

        return [
            'first' => $first->format(DateTime::ATOM),
            'last' => $last->format(DateTime::ATOM)
        ];
    }

    public function getBaseUrl()
    {
        return $this->apiUrl.'/albums';
    }
}
