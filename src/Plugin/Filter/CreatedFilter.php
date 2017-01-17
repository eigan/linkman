<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class CreatedFilter implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-created';
    }

    public function getDescription() : string
    {
        return 'Filters by date';
    }

    /**
     * Possible argument values:
     * >date
     * <date
     * date..date
     *
     */
    public function modify(QueryBuilder $queryBuilder, $argumentValue)
    {
        preg_match("/(<|>|)([\d-]+)(\.\.|)([\d-]+|)/", $argumentValue, $matches);

        $higherLower = $matches[1];
        $date1 = date_create_from_format('d-m-Y', $matches[2]);
        $date2 = date_create_from_format('d-m-Y', $matches[4]);

        switch ($higherLower) {
            case '>':
                $queryBuilder->andWhere('c.createdAt > :date1');
                $queryBuilder->setParameter('date1', $date1);
                $date1->setTime(23, 59, 59);
                break;

            case '<':
                $queryBuilder->andWhere('c.createdAt < :date1');
                $queryBuilder->setParameter('date1', $date1);
                $date1->setTime(0, 0, 0);
                break;

            default:
                // Nothing, either exact day, or between dates..
                $queryBuilder->andWhere('c.createdAt > :date1 and c.createdAt < :date2');
                $date1->setTime(0, 0, 0);
                $queryBuilder->setParameter('date1', $date1);

                if ($date2 == null) {
                    $date2 = clone $date1;
                    $date2->setTime(23, 59, 59);
                }

                $queryBuilder->setParameter('date2', $date2);

                break;
        }
    }
}
