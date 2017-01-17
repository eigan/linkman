<?php

namespace Linkman;

use Doctrine\Common\Collections\ArrayCollection;

class ContentQueryModifierCollection extends ArrayCollection
{
    public function getByType($type)
    {
        return array_filter($this->toArray(), function ($object) use ($type) {
            return $object instanceof $type;
        });
    }
}
