<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Day implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-day';
    }

    public function getDescription() : string
    {
        return 'Contents on this day';
    }

    /**
     * TODO: Convert month string (ex 'august') into month number
     */
    public function modify(QueryBuilder $query, $day)
    {
        if (empty($day)) {
            return;
        }

        $query->andWhere('DAY(c.createdAt) = :day');
        $query->setParameter('day', $day);
    }
}
