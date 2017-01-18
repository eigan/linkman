<?php

namespace Linkman\Plugin\Action;

use Linkman\Fileservice;
use Linkman\Plugin\ContentActionInterface;

use Traversable;

class Rename implements ContentActionInterface
{
    public function __construct(Fileservice $service)
    {
        $this->service = $service;
    }

    public function getName()
    {
        return 'rename';
    }

    public function getDescription()
    {
        return 'Renames the contents';
    }

    public function execute(Traversable $contents, $argValue)
    {
        foreach ($contents as $content) {
            foreach ($content->getFiles() as $file) {
                try {
                    $this->service->rename($file, $argValue);
                } catch (\InvalidArgumentException $e) {
                    // skip
                }
            }
        }
    }
}
