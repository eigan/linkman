<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Limit implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-limit';
    }

    public function getDescription() : string
    {
        return 'Limit the results';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        $query->setMaxResults((int) $argumentValue);
    }
}
