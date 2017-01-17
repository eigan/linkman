<?php

namespace Linkman\Exception;

use Exception;

class NotInitializedException extends Exception
{
    public function __construct($linkman)
    {
        parent::__construct('Linkman is not initialized. Please run initialize()');

        $this->linkman = $linkman;
    }

    public function getLinkman()
    {
        return $this->linkman;
    }
}
