<?php

namespace Linkman\Plugin;

interface ContentActionInterface
{
    public function getName();

    public function getDescription();

    public function execute(array $contents, $argValue);
}
