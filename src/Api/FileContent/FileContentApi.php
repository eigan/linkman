<?php

namespace Linkman\Api\FileContent;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Linkman\ContentQueryModifierCollection;
use Linkman\Domain\FileContent;

use Linkman\FilesystemResolver;
use Linkman\Repositories\FileContentRepository;

class FileContentApi
{
    private $repository;

    public function __construct(FileContentRepository $repository, FilesystemResolver $resolver, ContentQueryModifierCollection $filters)
    {
        $this->repository = $repository;
        $this->resolver = $resolver;
        $this->filters = $filters;
    }

    /**
     * @return FileContent
     */
    public function one(int $contentId)
    {
        return $this->repository->find($contentId);
    }

    /**
     * @return FileContent
     */
    public function all($query)
    {
        $mount = null;
        $relativePath = null;

        if (isset($options['path'])) {
            $mount = $this->resolveMount($options['path']);
            $relativePath = $this->filesystem($mount)->removePathPrefix($options['path']);
        }

        $queryBuilder = $this->repository->createQueryBuilder('c');
        $this->filter($queryBuilder, $query);
        $queryBuilder->andWhere('c.isHidden = false');
        $queryBuilder->join('c.files', 'f');

        if ($mount && $relativePath) {
            $queryBuilder->andWhere('f.mount = :mount');
            $queryBuilder->setParameter('mount', $mount);

            $queryBuilder->andWhere('f.path LIKE :relativePath');
            $queryBuilder->setParameter('relativePath', "$relativePath%");
        }

        return new Paginator($queryBuilder->getQuery());
    }

    public function paginate()
    {
        // Return pagination object
    }

    /**
     * @return resource
     * @throws FileNotFoundException
     */
    public function raw(FileContent $content)
    {
        foreach ($content->getFiles() as $file) {
            $filesystem = $this->resolver->resolve($file);

            if ($filesystem->has($file->getPath()) == false) {
                continue;
            }

            return fopen($filesystem->realpath($file->getPath()), 'r');
        }

        return null;
    }

    /**
     * @return resource
     */
    public function thumb(FileContent $content)
    {
        return $content->getThumbnail();
    }

    public function count()
    {
        $queryBuilder = $this->repository->createQueryBuilder('content');
        $queryBuilder->select('count(content.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * TODO: This should be moved
     */
    private function resolveMount($path)
    {
        // Find path which matches part of the string..
        $mounts = $this->mounts->findAll();

        // We only supports local, so here we just make sure filesystem root matches

        foreach ($mounts as $mount) {
            $filesystem = $this->filesystem($mount);

            if ($filesystem->has($filesystem->removePathPrefix($path))) {
                return $mount;
            }
        }
    }

    /**
     * More like a freetext search
     */
    public function search($queryText)
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');
        $queryBuilder->andWhere('c.isHidden = false');
        $queryBuilder->join('c.files', 'f');
        $queryBuilder->join('c.tags', 't');

        $tags = explode(',', $queryText);
        $tags = array_filter($tags);

        foreach ($tags as &$tag) {
            $tag = trim($tag);
        }

        if ($tags) {
            $queryBuilder->andWhere('t.name IN (:tags)');
            $queryBuilder->setParameter('tags', $tags);

            $queryBuilder->groupBy('c.id');
            $queryBuilder->having('COUNT(DISTINCT t.name) = ' . count($tags));
        }

        return new Paginator($queryBuilder);
    }

    private function filter(QueryBuilder $queryBuilder, $query)
    {
        foreach ($query as $filterKey => $filterValue) {
            if (isset($this->filters[$filterKey]) == false) {
                // Throw exception?
                continue;
            }

            $this->filters[$filterKey]->modify($queryBuilder, $filterValue);
        }
    }

    public function yearsCount()
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');

        $queryBuilder->select('count(c.id) as num');
        $queryBuilder->addSelect('YEAR(c.createdAt) as y');
        $queryBuilder->groupBy('y');

        $years = $queryBuilder->getQuery()->getScalarResult();

        return array_map(function($year) {
            return [
                'year' => (int) $year['y'],
                'count' => (int) $year['num']
            ];
        }, $years);
    }

    public function monthsCount($year)
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');

        $queryBuilder->select('count(c.id) as num');
        $queryBuilder->addSelect('YEAR(c.createdAt) as y');
        $queryBuilder->addSelect('MONTH(c.createdAt) as m');
        $queryBuilder->groupBy('y, m');
        $queryBuilder->andWhere('y = :year');
        $queryBuilder->setParameter('year', $year);

        $years = $queryBuilder->getQuery()->getScalarResult();

        return array_map(function($year) {
            return [
                'year' => (int) $year['y'],
                'month' => (int) $year['m'],
                'count' => (int) $year['num']
            ];
        }, $years);
    }

    public function daysCount($year, $month)
    {
        $queryBuilder = $this->repository->createQueryBuilder('c');

        $queryBuilder->select('count(c.id) as num');
        $queryBuilder->addSelect('YEAR(c.createdAt) as y');
        $queryBuilder->addSelect('MONTH(c.createdAt) as m');
        $queryBuilder->addSelect('DAY(c.createdAt) as d');
        $queryBuilder->groupBy('y, m, d');
        $queryBuilder->andWhere('y = :year');
        $queryBuilder->setParameter('year', $year);

        $queryBuilder->andWhere('m = :month');
        $queryBuilder->setParameter('month', $month);

        $years = $queryBuilder->getQuery()->getScalarResult();

        return array_map(function($year) {
            return [
                'year' => (int) $year['y'],
                'month' => (int) $year['m'],
                'day' => (int) $year['d'],
                'count' => (int) $year['num']
            ];
        }, $years);
    }
}
