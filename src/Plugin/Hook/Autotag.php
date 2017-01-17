<?php

namespace Linkman\Plugin\Hook;

use Linkman\Domain\File;

use Linkman\Domain\FileContent;
use Linkman\Tagservice;

class Autotag
{
    public function __construct(Tagservice $tagService)
    {
        $this->service = $tagService;
    }

    public function tag(FileContent $content, File $file)
    {
        $tags = [];
        $createdAt = $content->getCreatedAt();
        $hour = $createdAt->format('H');

        if ($hour > 0 && $hour < 6) {
            $this->service->tag($content, 'night');
        }

        if ($hour > 6 && $hour < 9) {
            $this->service->tag($content, 'morning');
        }

        if ($hour > 9 && $hour < 17) {
            $this->service->tag($content, 'day');
        }

        if ($hour > 17 && $hour < 24) {
            $this->service->tag($content, 'evening');
        }

        // Month
        $this->service->tag($content, $createdAt->format('F'));
        // year
        $this->service->tag($content, $createdAt->format('Y'));
        // weekday
        $this->service->tag($content, $createdAt->format('l'));

        $path = $file->getPath();
        $pathParts = explode('/', $path);

        array_shift($pathParts); //removes first
        array_pop($pathParts); //removes last

        foreach ($pathParts as $pathPart) {
            $this->service->tag($content, $pathPart);
        }
    }
}
