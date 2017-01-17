<?php

namespace Linkman\Domain;

class Mount
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $adapterName;

    /**
     * @var array
     */
    protected $config;

    /**
     *
     */
    protected $name;

    /**
     * @var File[]
     */
    protected $files;

    /**
     *
     */
    public function __construct($name, $adapterName, array $config = [])
    {
        $this->name = $name;
        $this->adapterName = $adapterName;
        $this->config = $config;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAdapterName()
    {
        return $this->adapterName;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getConfigValue($configKey)
    {
        return $this->config[$configKey];
    }
}
