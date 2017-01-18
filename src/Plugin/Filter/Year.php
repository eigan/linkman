<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Year implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-year';
    }

    public function getDescription() : string
    {
        return 'Contents in this year';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        if (empty($argumentValue)) {
            return;
        }

        $query->andWhere('YEAR(c.createdAt) = :year');
        $query->setParameter('year', $argumentValue);
    }
}
