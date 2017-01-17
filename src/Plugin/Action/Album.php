<?php

namespace Linkman\Plugin\Action;

use Linkman\Api\Api;

use Linkman\Plugin\ContentActionInterface;

/**
 * Make album out of the selection
 */
class Album implements ContentActionInterface
{
    /**
     * @var Api
     */
    private $api;

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function getName()
    {
        return 'album';
    }

    public function getDescription()
    {
        return 'Make album of the selection';
    }

    public function execute(array $contents, $argValue)
    {
        $album = $this->api->albums->create($argValue);

        foreach ($contents as $content) {
            $album->addContent($content);
        }
    }
}
