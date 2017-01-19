<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Album implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-album';
    }

    public function getDescription() : string
    {
        return 'Contents in this album';
    }

    /**
     * TODO: Convert month string (ex 'august') into month number
     */
    public function modify(QueryBuilder $query, $album)
    {
        if (empty($album)) {
            return;
        }

        $query->join('c.albums', 'a');

        if(is_numeric($album)) {
            $query->andWhere('a.id = :albumId');
            $query->setParameter('albumId', $album);
        } else {
            $query->andWhere('a.title = :album');
            $query->setParameter('album', $album);
        }
    }
}
