<?php

namespace Linkman\Console\Input;

use Symfony\Component\Console\Input\InputOption;

class PluginInputOption extends InputOption
{
    protected $callable;

    public function __construct($name, $callable)
    {
        // TODO: Get the default argumentValue directly from type hint(?)
        parent::__construct($name, null, InputOption::VALUE_OPTIONAL, $callable->getDescription());

        $this->callable = $callable;
    }

    public function getCallable()
    {
        return $this->callable;
    }
}
