<?php

namespace Linkman\Plugin\Order;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentOrderInterface;

class Latest implements ContentOrderInterface
{
    public function getName() : string
    {
        return 'order-latest';
    }

    public function getDescription() : string
    {
        return 'Order by latest (modified)';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        $query->orderBy('c.modifiedAt', 'desc');
    }
}
