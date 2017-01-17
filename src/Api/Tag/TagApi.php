<?php

namespace Linkman\Api\Tag;

use Linkman\Repositories\TagRepository;
use Linkman\Tagservice;

class TagApi
{
    public function __construct(Tagservice $service, TagRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function one()
    {
    }

    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * Returns those that are used with $also
     * Weird name i know..
     */
    public function also($also)
    {
        // select * from content_tag where
        return $this->all();
    }

    public function create()
    {
    }

    public function service()
    {
        return $this->service;
    }
}
