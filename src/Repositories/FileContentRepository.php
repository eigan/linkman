<?php

namespace Linkman\Repositories;

class FileContentRepository extends EntityRepository
{
    private $cached = [];

    public function latest($limit = 5)
    {
        return $this->findBy([], ['modifiedAt' => 'DESC'], $limit);
    }

    public function byHash($hash)
    {
        if(isset($this->cached[$hash])) {
            return $this->cached[$hash];
        }

        return $this->findOneBy(['hash' => $hash]);
    }

    public function persist($content)
    {    
        $this->getEntityManager()->persist($content);
        $this->cached[$content->getHash()] = $content;
    }
}
