<?php

namespace Linkman\Repositories;

use Doctrine\ORM\EntityRepository;

use Linkman\Domain\Tag;

class TagRepository extends EntityRepository
{
    private $localCache = [];

    public function create($tagName)
    {
        $tag = new Tag($tagName);

        $this->localCache[$tag->getName()] = $tag;

        $this->getEntityManager()->persist($tag);

        return $tag;
    }

    /**
     * @return Tag|null
     */
    public function findByName($tagName)
    {
        $tagName = strtolower($tagName);

        return $this->localCache[$tagName] ?? $this->findOneBy(['name' => $tagName]);
    }
}
