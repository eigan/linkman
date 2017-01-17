<?php

namespace Linkman\Repositories;

use Doctrine\ORM\EntityRepository;

use Linkman\Domain\Mount;

class FileRepository extends EntityRepository
{
    protected $filesByHash = [];
    protected $filesByPath = [];

    public function byPath($path, Mount $mount)
    {
        $path = ltrim($path, '/');

        if (isset($this->filesByPath[$path])) {
            return $this->filesByPath[$path];
        }

        return $this->findOneBy(['path' => $path, 'mount' => $mount]);
    }

    public function byHash($hash)
    {
        if (isset($this->filesByHash[$hash])) {
            return $this->filesByHash[$hash];
        }

        return $this->findOneBy(['hash' => $hash]);
    }

    public function pathForHash($hash)
    {
        $files = $this->findBy(['hash' => $hash]);

        return reset($files)->getPath();
    }

    public function all()
    {
        return $this->findAll();
    }

    public function latest($limit = 5)
    {
        return $this->findBy([], ['updatedAt' => 'DESC'], $limit);
    }

    public function cacheAll()
    {
        $this->filesByHash = $this->filesByPath = [];

        $files = $this->findAll();

        foreach ($files as $file) {
            $this->filesByHash[$file->getHash()] = $file;
            $this->filesByPath[$file->getPath()] = $file;
        }
    }
}
