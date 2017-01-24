<?php

namespace Linkman\Api\File;

use DateTime;

use Linkman\Domain\File;

use Linkman\Domain\FileContent;
use Linkman\Domain\Mount;
use Linkman\FilesystemResolver;

use Linkman\Repositories\FileRepository;

class FileApi
{

    /**
     * @var FileRepository
     */
    protected $repository;

    public function __construct(FilesystemResolver $filesystemResolver, FileRepository $repository)
    {
        $this->resolver = $filesystemResolver;
        $this->repository = $repository;
    }

    public function query($alias = 'file')
    {
        return $this->repository->createQueryBuilder($alias);
    }

    /**
     *
     */
    public function list(Mount $mount, $path = '')
    {
        $filesystem = $this->resolver->resolve($mount);

        $dirContents = [];

        $contents = $filesystem->listContents($path);

        foreach ($contents as $content) {
            $data = $content;

            $data['mount'] = [
                'id' => $mount->getId()
            ];

            if ($content['type'] != 'dir') {
                $file = $this->repository->byPath($content['path'], $mount);

                if ($file instanceof File) {
                    $data['synced'] = $file->getLastSynced() >= new DateTime(date('c', $content['timestamp']));
                    $data['content'] = [
                        'id' => $file->getContent()->getId()
                    ];
                }
            }

            $dirContents[] = $data;
        }

        return $dirContents;
    }

    public function one($fileId)
    {
        return $this->repository->find($fileId);
    }

    public function byPath($path = '', Mount $mount)
    {
        $filesystem = $this->resolver->resolve($mount);

        $info = $filesystem->getWithMetadata($path, ['timestamp', 'mimetype']);

        $file = $this->repository->byPath($path, $mount);

        if ($file instanceof File) {
            $info['fileIsSynced'] = $file->getLastSynced() >= new DateTime(date('c', $info['timestamp']));
            $info['content'] = $file->getContent();
        }
        return $info;
    }

    /**
     * @return Content
     */
    public function contents(Mount $mount, $path = '')
    {
        $list = $this->list($mount, $path);

        $list = array_filter($list, function () {
            return $list['content'] instanceof FileContent;
        });

        return array_map(function ($file) {
            return $file['content'];
        }, $list);
    }

    public function raw($mount, $path)
    {
        $filesystem = $this->resolver->resolve($mount);
        return $filesystem->readStream($path);
    }

    public function count()
    {
        $queryBuilder = $this->repository->createQueryBuilder('file');
        $queryBuilder->select('count(file.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
