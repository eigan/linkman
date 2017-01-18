<?php

namespace Linkman\Plugin;

use Linkman\Api\Api;
use Linkman\Fileservice;

use Linkman\Tagservice;

class CorePlugin extends Plugin
{
    public function __construct(Api $api, Fileservice $service, Tagservice $tagservice)
    {
        $this->api = $api;
        $this->fileservice = $service;
        $this->tagservice = $tagservice;
    }

    public function hooks()
    {
        return [
            'sync' => [
                [new Hook\Exif($this->fileservice, $this->tagservice), 'tag'],
                [new Hook\Autotag($this->tagservice), 'tag']
            ]
        ];
    }

    public function register($register)
    {
        $register->use(new Filter\CreatedFilter());
        $register->use(new Filter\Day());
        $register->use(new Filter\Limit());
        $register->use(new Filter\Name());
        $register->use(new Filter\Offset());
        $register->use(new Filter\Tag());
        $register->use(new Filter\Type());
        $register->use(new Filter\Year());
        $register->use(new Filter\Month());
        $register->use(new Order\Latest());
        $register->use(new Order\Created());
        $register->use(new Order\Modified());
        $register->use(new Action\Rename($this->fileservice));
        $register->use(new Action\Tag($this->tagservice));
        $register->use(new Action\Album($this->api));
        $register->use(new Output\ConsoleTable());
        $register->use(new Output\Json());
    }
}
