<?php

namespace Linkman\Api\Mount;

use Doctrine\ORM\EntityManagerInterface;

use Linkman\Domain\Mount;

use Linkman\FilesystemResolver;

class MountApi
{
    public function __construct(EntityManagerInterface $entityManager, FilesystemResolver $resolver)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Mount::class);
        $this->resolver = $resolver;
    }

    public function one($mountId)
    {
        return $this->repository->find($mountId);
    }

    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * Create a mount at this location
     */
    public function create($target, $name, $config = [])
    {
        $adapter = $this->readAdapter($target, $config);

        $mount = new Mount($name, $adapter[0], $adapter[1]);

        $this->resolver->resolve($mount); // Tests connect to filesystem

        $this->entityManager->persist($mount);
        $this->entityManager->flush();

        return $mount;
    }

    /**
     * Temporary until I sort stuff out..
     */
    private function readAdapter($target, $config)
    {
        return ['local', ['root' => $target]];
    }
}
