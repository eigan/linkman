<?php

namespace Linkman\Plugin\Order;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentOrderInterface;

class Modified implements ContentOrderInterface
{
    public function getName() : string
    {
        return 'order-modified';
    }

    public function getDescription() : string
    {
        return 'Order by modified date';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        if (empty($argumentValue)) {
            throw new \Exception('Missing desc or asc for --order-modified');
        }

        $query->orderBy('c.modifiedAt', $argumentValue);
    }
}
