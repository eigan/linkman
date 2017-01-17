<?php

namespace Linkman\Http\Response;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatedResponse extends CollectionResponse
{
    // Send items to parent construct
    // Set headers?

    private $nextLink;

    private $previousLink;

    public function __construct(Paginator $paginator, $formatter)
    {
        parent::__construct($paginator, $formatter);

        $this->headers->set('X-Total-Count', $paginator->count());
    }

    public function setNextLink($link)
    {
        $this->nextLink = $link;
    }

    public function setPreviousLink($link)
    {
        $this->previousLink = $link;
    }

    public function sendHeaders()
    {
        $linkHeader = [];

        if ($this->nextLink) {
            $linkHeader[] = '<'.$this->nextLink.'>; rel="next"';
        }

        if ($this->previousLink) {
            $linkHeader[] = '<'.$this->previousLink.'>; rel="previous"';
        }

        if ($linkHeader) {
            $this->headers->add('Link', implode(',', $linkHeader));
        }

        parent::sendHeaders();
    }
}
