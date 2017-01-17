<?php

namespace Linkman\Http\Formatter;

abstract class AbstractFormatter implements FormatterInterface
{
    const FULL = 'full';
    const EMBED = 'embed';

    public function __construct($apiUrl, $embeds = [])
    {
        $this->apiUrl = $apiUrl;
        $this->embeds = $embeds;
    }

    public function getHref($entity)
    {
        return $this->getBaseUrl().'/'.$entity->getId();
    }

    public function embedMode($embedKey)
    {
        if (in_array($embedKey, $this->embeds)) {
            return self::FULL;
        }

        return self::EMBED;
    }

    abstract public function getBaseUrl();
}
