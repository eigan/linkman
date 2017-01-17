<?php

namespace Linkman\Api\Album;

use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Linkman\Domain\Album;
use Linkman\Domain\FileContent;

class AlbumApi
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Album::class);
        $this->entityManager = $entityManager;
    }

    public function all()
    {
        return $this->repository->findAll();
    }

    public function one($albumId)
    {
        return $this->repository->find($albumId);
    }

    public function create($title)
    {
        $album = new Album($title);
        $this->entityManager->persist($album);

        return $album;
    }

    /**
     * @return Paginator
     */
    public function contents($albumId)
    {
        $album = $this->one($albumId);
        $builder = $this->entityManager->getRepository(FileContent::class)->createQuerybuilder('content');

        $builder->where(':albumId MEMBER OF content.albums');
        $builder->setParameter('albumId', $album);

        return new Paginator($builder);
    }
}
