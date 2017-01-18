<?php

namespace Linkman\Api\Tag;

use Linkman\Repositories\TagRepository;
use Linkman\Tagservice;

class TagApi
{

    /**
     * @var Tagservice
     */
    protected $service;

    /**
     * @var TagRepository
     */
    protected $repository;

    public function __construct(Tagservice $service, TagRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function one($tagName)
    {
        return $this->repository->findByName($tagName);
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
        return $this->all();
    }

    public function create($tagName)
    {
        return $this->repository->create($tagName);
    }

    public function service()
    {
        return $this->service;
    }
}
