<?php

namespace Linkman\Domain;

use InvalidArgumentException;

class PluginDefinition
{
    protected $id;

    protected $className;

    protected $options;

    public function __construct($className, $options)
    {
        if (class_exists($className) == false) {
            throw new InvalidArgumentException("The classname [$className] doesnt exist");
        }

        $this->className = $className;
        $this->options = $options;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
