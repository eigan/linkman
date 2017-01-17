<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Name implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-name';
    }

    public function getDescription() : string
    {
        return 'Match the path of the file';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        $query->andWhere('f.path like :nameLimitArgValue');
        $query->setParameter('nameLimitArgValue', "%$argumentValue%");
    }
}
