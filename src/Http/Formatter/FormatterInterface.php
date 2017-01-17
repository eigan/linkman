<?php

namespace Linkman\Http\Formatter;

interface FormatterInterface
{
    public function format($entity) : array;
}
