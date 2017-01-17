<?php

namespace Linkman\Http\Formatter\Domain;

use Linkman\Http\Formatter\FormatterInterface;

class TagFormatter implements FormatterInterface
{
    public function format($tag) : array
    {
        return [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'title' => $tag->getDisplayName(),
            'usage' => $tag->countContents()
        ];
    }
}
