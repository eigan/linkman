<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Duplicated implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-duplicated';
    }

    public function getDescription() : string
    {
        return 'Contents that have multiple files';
    }

    /**
     * TODO: Convert month string (ex 'august') into month number
     */
    public function modify(QueryBuilder $query, $argValue)
    {
        $query->join("c.files", "files");
        $query->groupBy("c.id");
        $query->having("count(files) > 1");
    }
}
