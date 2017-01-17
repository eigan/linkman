<?php

namespace Linkman\Plugin\Filter;

use Doctrine\ORM\QueryBuilder;

use Linkman\Plugin\ContentQueryModifierInterface;

class Type implements ContentQueryModifierInterface
{
    public function getName() : string
    {
        return 'filter-type';
    }

    public function getDescription() : string
    {
        return 'Only of given type';
    }

    public function modify(QueryBuilder $query, $argumentValue)
    {
        if (empty($argumentValue)) {
            return;
        }

        switch ($argumentValue) {
            case 'photo':
                $className = \Linkman\Domain\Photo::class;
                break;

            default:
                $className = \Linkman\Domain\UnknownContentType::class;
                break;
        }

        $query->andWhere('c INSTANCE OF ' . $className);
    }
}
