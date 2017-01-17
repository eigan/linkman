<?php

namespace Linkman\Http\Response;

use Linkman\Http\Formatter\FormatterInterface;
use Opulence\Http\Responses\JsonResponse;
use Opulence\Http\Responses\ResponseHeaders;

class CollectionResponse extends JsonResponse
{
    public function __construct($entities, FormatterInterface $formatter, $headers = [])
    {
        $this->formatter = $formatter;

        parent::__construct($entities, ResponseHeaders::HTTP_OK, $headers);
    }

    // Count: ..
    // Current page: ...
    // Link: Next
    // Link: Previous

    public function setContent($entities)
    {
        // array of items
        $content = [];

        foreach ($entities as $entity) {
            $content[] = $this->formatter->format($entity);
        }

        return parent::setContent($content);
    }
}
