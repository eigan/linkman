<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Month implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-month';
    }

    public function getDescription() : string
    {
        return 'Contents in this month';
    }

    /**
     * TODO: Convert month string (ex 'august') into month number
     */
    public function modify(QueryBuilder $query, $month)
    {
        if (empty($month)) {
            return;
        }

        $query->andWhere('MONTH(c.createdAt) = :month');
        $query->setParameter('month', $month);
    }
}
