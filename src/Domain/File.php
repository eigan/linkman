<?php

namespace Linkman\Domain;

use DateTime;

class File
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Mount
     */
    protected $mount;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var FileContent
     */
    protected $content;

    /**
     * @var DateTime
     */
    protected $synced;

    /**
     *
     * @param string      $path
     * @param FileContent $content
     * @param DateTime    $synced
     */
    public function __construct(Mount $mount, string $path, FileContent $content, DateTime $synced)
    {
        $this->mount = $mount;
        $this->path = $path;
        $this->content = $content;

        $this->synced = $synced;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMount() : Mount
    {
        return $this->mount;
    }

    /**
     * Get the Datetime for last synced date
     *
     * @return DateTime
     */
    public function getLastSynced() : DateTime
    {
        return $this->synced;
    }

    /**
     * Set the Datetime for last synced date
     *
     * @param DateTime $time
     */
    public function setLastSynced(DateTime $time)
    {
        $this->synced = $time;
    }

    /**
     * @return FileContent
     */
    public function getContent() : FileContent
    {
        return $this->content;
    }

    /**
     * The relative path to the file
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getDirectoryPath()
    {
        // TODO: Remove first dot.
        return dirname($this->getPath());
    }

    /**
     * @return string
     */
    public function getExtension() : string
    {
        return pathinfo($this->getPath(), PATHINFO_EXTENSION);
    }

    public function getFilename()
    {
        return pathinfo($this->getPath(), PATHINFO_FILENAME);
    }

    public function getModifiedAt()
    {
        return $this->getContent()->getModifiedAt();
    }
}
