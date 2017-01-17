<?php

namespace Linkman\Http\Response;

use Linkman\Http\Formatter\FormatterInterface;
use Opulence\Http\Responses\JsonResponse;

class EntityResponse extends JsonResponse
{
    public function __construct($entity, FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        parent::__construct($entity);
    }

    public function setContent($entity)
    {
        return parent::setContent($this->formatter->format($entity));
    }
}
