<?php

namespace Linkman\Domain;

/**
 * A tag describe FileContent
 */
class Tag
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var FileContent
     */
    protected $contents;

    /**
     * Use this to store the owner / creator of the tag.
     * This will let tet you control your own tags in plugins / filters
     *
     * @var string
     */
    protected $owner;

    public function __construct($name, string $owner = null)
    {
        $this->name = strtolower($name);
        $this->displayName = $name;
        $this->owner = $owner;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function countContents()
    {
        return $this->contents->count();
    }
}
