<?php

namespace Linkman\Api;

use Doctrine\ORM\EntityManagerInterface;
use League\Container\Container;

use Linkman\Api\Album\AlbumApi;
use Linkman\Api\File\FileApi;
use Linkman\Api\FileContent\FileContentApi;
use Linkman\Api\Mount\MountApi;

use Linkman\Api\Tag\TagApi;
use Linkman\Domain\Album;
use Linkman\Domain\FileContent;
use Linkman\Domain\Mount;

/**
 * Dig into the core of the db with these simple methods..
 */
class Api
{
    private $map = [
        'contents' => FileContentApi::class,
        'albums' => AlbumApi::class,
        'files' => FileApi::class,
        'mounts' => MountApi::class,
        'tags' => TagApi::class,
    ];

    private $resolved = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return Album[]
     */
    public function albums()
    {
        return $this->albums->all();
    }

    /**
     * @return Album
     */
    public function album($albumId)
    {
        return $this->albums->one($albumId);
    }

    /**
     * @return FileContent[]
     */
    public function contents($data)
    {
        return $this->contents->all($data);
    }

    /**
     * @return FileContent
     */
    public function content(int $contentId)
    {
        return $this->contents->one($contentId);
    }

    /**
     * @return resource
     */
    public function contentRaw(int $contentId)
    {
        $content = $this->content($contentId);

        return $this->contents->raw($content);
    }

    /**
     * @return Mount
     */
    public function mount(int $mountId)
    {
        return $this->mounts->one($mountId);
    }

    public function mounts()
    {
        return $this->mounts->all();
    }

    public function file($fileId)
    {
        return $this->files->one($fileId);
    }

    /**
     * @return array of files/directories directly on the mount now
     */
    public function files($mountId, string $path = '/')
    {
        $mount = $this->mount($mountId);

        return $this->files->list($mount, $path);
    }

    public function fileRaw($mountId, $path)
    {
        $mount = $this->mount($mountId);

        return $this->files->raw($mount, $path);
    }

    public function resource($contentId)
    {
        $content = $this->content($contentId);
    }

    public function flush()
    {
        $entityManager = $this->container->get(EntityManagerInterface::class);

        return $entityManager->flush();
    }

    public function __get($api)
    {
        if (isset($this->resolved[$api])) {
            return $this->resolved[$api];
        }

        if (isset($this->map[$api]) == false) {
            throw new \InvalidArgumentException("Invalid Api [$api]");
        }

        return $this->resolved[$api] = $this->container->get($this->map[$api]);
    }
}
