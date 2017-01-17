<?php

namespace Linkman\Domain;

class User
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
    protected $password;

    /**
     * Favorited file contents
     */
    protected $favorites;

    public function getId()
    {
        return $this->id;
    }
}
