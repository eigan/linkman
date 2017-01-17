<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Tag implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-tag';
    }

    public function getDescription() : string
    {
        return 'Filter by comma separated list of tags';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        if (empty($argumentValue)) {
            return;
        }

        $tags = explode(',', $argumentValue);
        $query->leftJoin('c.tags', 't');

        foreach ($tags as &$tag) {
            $tag = trim($tag);
        }

        $query->andWhere('t.name IN (:tags)');
        $query->setParameter('tags', $tags);
        $query->groupBy('c.id');
        $query->having('COUNT(DISTINCT t.name) = ' . count($tags));

        //die($query->getQuery()->getDql());
    }
}
