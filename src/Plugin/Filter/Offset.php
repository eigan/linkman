<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Offset implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-offset';
    }

    public function getDescription() : string
    {
        return 'Offset the results';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        $query->setFirstResult((int) $argumentValue);
    }
}
