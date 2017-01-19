<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class NoTag implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-no-tags';
    }

    public function getDescription() : string
    {
        return 'Filter by comma separated list of tags';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        $query->leftJoin('c.tags', 't');

        $query->groupBy('c.id');
        $query->having('count(t.id) = 0');
    }
}
