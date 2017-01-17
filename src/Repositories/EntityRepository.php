<?php

namespace Linkman\Repositories;

use Doctrine\ORM\EntityRepository as DoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class EntityRepository extends DoctrineRepository
{
    public function findAll()
    {
        $queryBuilder = $this->createQueryBuilder('entity');

        return new Paginator($queryBuilder);
    }
}
