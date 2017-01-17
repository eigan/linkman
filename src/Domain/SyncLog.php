<?php

namespace Linkman\Domain;

use DateTime;

class SyncLog
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * One of SyncLog::STATUS_*
     *
     * @var int
     */
    public $status;

    const STATUS_OK = 1;
    const STATUS_FAILED = 0;

    /**
     * @param DateTime $createdAt
     * @param int      $status
     */
    public function __construct(DateTime $createdAt, int $status)
    {
        $this->createdAt = $createdAt;
        $this->status    = $status;
    }

    /**
     * @return int
     */
    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt() : DateTime
    {
        return $this->createdAt;
    }
}
