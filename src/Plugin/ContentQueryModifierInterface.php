<?php

namespace Linkman\Plugin;

use Doctrine\ORM\QueryBuilder;

/**
 * Alters the contents query
 */
interface ContentQueryModifierInterface
{
    /**
     * @return string The name to be used for enabling this modifier
     */
    public function getName() : string;

    /**
     * @return string What the query does, and what it accepts as argument value
     */
    public function getDescription() : string;

    /**
     * Use the QueryBuilder to alter the query here
     *
     * @return null
     */
    public function modify(QueryBuilder $queryBuilder, $argValue);
}
