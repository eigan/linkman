<?php

namespace Linkman\Plugin;

use Traversable;

interface ContentOutputInterface
{
    public function getName();

    public function getDescription();

    public function execute(Traversable $contents, $argValue);
}
