<?php

namespace Linkman\Repositories;

class FileContentRepository extends EntityRepository
{
    public function latest($limit = 5)
    {
        return $this->findBy([], ['modifiedAt' => 'DESC'], $limit);
    }

    public function byHash($hash)
    {
        return $this->findOneBy(['hash' => $hash]);
    }
}
