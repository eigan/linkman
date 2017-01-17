<?php

namespace Linkman\Domain;

use DateTime;

use Doctrine\Common\Collections\ArrayCollection;

abstract class FileContent
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var array
     */
    protected $files;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $modifiedAt;

    /**
     * @var Tag[]
     */
    protected $tags;

    /**
     * TODO: Implement sizecheck on sync
     *
     * @var int
     */
    protected $size;

    /**
     *
     */
    protected $filetype;

    /**
     * @var bool
     */
    protected $isHidden;

    /**
     * @var resource
     */
    protected $thumbnail;

    /**
     * @var Album[]
     */
    protected $albums;

    /**
     * @param string   $hash
     * @param DateTime $modifiedAt
     */
    final public function __construct(string $hash, $filetype, DateTime $modifiedAt)
    {
        $this->hash = $hash;
        $this->modifiedAt = $modifiedAt;
        $this->createdAt = clone $modifiedAt;
        $this->filetype = $filetype;

        $this->collections = [];

        $this->tags = new ArrayCollection();
        $this->isHidden = false;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     *
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * @param string $path
     */
    public function getCollectionByPath(string $path = null)
    {
        foreach ($this->collections as $collection) {
            if ($collection->getPath() == $path) {
                return $collection;
            }
        }

        return null;
    }

    /**
     * @param DateTime $modifiedAt
     */
    public function setModifiedAt(DateTime $modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return DateTime
     */
    public function getModifiedAt() : DateTime
    {
        return $this->modifiedAt;
    }

    public function addTag(Tag $tag)
    {
        if (isset($this->tags[$tag->getName()])) {
            return;
        }

        $this->tags->set($tag->getName(), $tag);
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getTagNames()
    {
        return $this->tags->getKeys();
    }

    public function hasTag($tagName)
    {
        return isset($this->tags[$tagName]);
    }

    public function getSize()
    {
        return $this->size;
    }

    public function hide()
    {
        $this->isHidden = true;
    }

    public function isHidden() : bool
    {
        return $this->isHidden;
    }

    public function setHidden(bool $hidden)
    {
        $this->isHidden = $hidden;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getFileType()
    {
        return $this->filetype;
    }

    public function getAlbums()
    {
        return $this->albums;
    }
}
