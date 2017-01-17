<?php

namespace Linkman\Console;

use Symfony\Component\Console\Command\Command;

class LinkmanCommand extends Command
{
    public function __construct($linkman, $name = null)
    {
        $this->linkman = $linkman;
        
        parent::__construct($name);
    }
}
