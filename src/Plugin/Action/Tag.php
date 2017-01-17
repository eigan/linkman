<?php

namespace Linkman\Plugin\Action;

use Linkman\Plugin\ContentActionInterface;

use Linkman\Tagservice;

class Tag implements ContentActionInterface
{
    private $service;

    public function __construct(Tagservice $service)
    {
        $this->service = $service;
    }

    public function getName()
    {
        return 'tag';
    }

    public function getDescription()
    {
        return 'Adds a tag';
    }

    public function execute(array $contents, $argValue)
    {
        foreach ($contents as $content) {
            $this->service->tag($content, $argValue);
        }
    }
}
