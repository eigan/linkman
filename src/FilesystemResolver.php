<?php

namespace Linkman;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local;

use Linkman\Domain\File;
use Linkman\Domain\Mount;

class FilesystemResolver
{
    protected $resolved = [];

    public function resolve($object) : Filesystem
    {
        $mount = $object;

        if ($object instanceof File) {
            $mount = $object->getMount();
        }

        if ($mount instanceof Mount == false) {
            throw new InvalidArgumentException('Object should be of class File or Mount');
        }

        if (isset($this->resolved[$mount->getId()])) {
            return $this->resolved[$mount->getId()];
        }

        switch ($mount->getAdapterName()) {
            case 'local':
                $adapter = new Local($mount->getConfigValue('root'));
            break;
        }

        $filesystem = new Filesystem($adapter);
        $filesystem->addPlugin(new \League\Flysystem\Plugin\GetWithMetadata());

        return $this->resolved[$mount->getId()] = $filesystem;
    }
}
