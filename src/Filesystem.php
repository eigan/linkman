<?php

namespace Linkman;

use DateTime;

use League\Flysystem\Adapter\Local;

use League\Flysystem\Filesystem as LeagueFilesystem;

use Linkman\Domain\Photo;
use Linkman\Domain\UnknownContentType;

class Filesystem extends LeagueFilesystem
{
    public function __construct(Local $adapter, $config = [])
    {
        parent::__construct($adapter, $config);
    }

    public function removePathPrefix($path)
    {
        $root = $this->getAdapter()->getPathPrefix();
        if (strpos($path, $root) === 0) {
            return str_replace($root, '', $path);
        }

        return $path;
    }

    public function isReadable(string $relativePath)
    {
        return is_readable($this->adapter->applyPathPrefix($relativePath));
    }

    public function resolveContent(string $relativePath)
    {
        // Get file type
        // Get hash
        $hash = $this->hashOf($relativePath);
        $size = $this->getSize($relativePath);
        $modifiedAt = $this->modified($relativePath);
        $realpath = $this->realpath($relativePath);

        $mime = mime_content_type($realpath);

        $type = substr($mime, 0, strpos($mime, '/'));

        switch ($type) {
            case 'image':
                $content = new Photo($hash, $mime, $modifiedAt);
            break;

            default:
                $content =  new UnknownContentType($hash, $mime, $modifiedAt);
                break;
        }

        $content->setSize($size);

        return $content;
    }

    public function hashOf(string $relativePath)
    {
        return @hash_file('md5', $this->adapter->applyPathPrefix($relativePath));
    }

    public function modified(string $relativePath)
    {
        return new DateTime(date('c', filemtime($this->adapter->applyPathPrefix($relativePath))));
    }

    public function countFiles()
    {
        $fileIterator = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator(
                                    $this->adapter->getPathPrefix(),
                                    \FilesystemIterator::SKIP_DOTS
                            )
                        );

        return iterator_count($fileIterator);
    }

    public function realpath($path)
    {
        return $this->adapter->applyPathPrefix($path);
    }
}
