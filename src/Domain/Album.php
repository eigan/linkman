<?php

namespace Linkman\Domain;

use DateTime;

use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 */
class Album
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var FileContent[]
     */
    protected $contents;

    /**
     * These are the ones who have permissions
     *
     * @var User
     */
    protected $createdBy;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * Noone has access after this date
     *
     * @var DateTime
     */
    protected $expireAt;

    /**
     * @var string mountId:path
     */
    protected $pathMap;

    public function __construct($title)
    {
        $this->title = $title;
        $this->createdAt = new DateTime();

        $this->contents = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function addContent(FileContent $content)
    {
        if ($this->contents->contains($content)) {
            return;
        }

        $this->contents->add($content);
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}
