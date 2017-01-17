<?php

namespace Linkman\Plugin\Order;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentOrderInterface;

class Created implements ContentOrderInterface
{
    public function getName() : string
    {
        return 'order-created';
    }

    public function getDescription() : string
    {
        return 'Order by created date';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        if (empty($argumentValue)) {
            throw new \Exception('Missing desc or asc for --order-created');
        }

        $query->orderBy('c.createdAt', $argumentValue);
    }
}
