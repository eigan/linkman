<?php

namespace Linkman\Http\Formatter\Domain;

use Linkman\Domain\Mount;
use Linkman\Http\Formatter\AbstractFormatter;

class MountFormatter extends AbstractFormatter
{
    /**
     * @param Mount $mount
     */
    public function format($mount, $mode = self::FULL) : array
    {
        $format = [
            'href' => $this->getHref($mount),
            'id' => $mount->getId(),
        ];

        if ($mode == self::FULL) {
            $format += [
                'name' => $mount->getName(),
                'browse' => [
                    'href' => $this->apiUrl . '/browse?mount='.$mount->getId()
                ],
                '$adapter' => $mount->getAdapterName()
            ];
        }

        return $format;
    }

    public function getBaseUrl()
    {
        return $this->apiUrl.'/mounts';
    }
}
