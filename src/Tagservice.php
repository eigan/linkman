<?php

namespace Linkman;

use Linkman\Domain\FileContent;

use Linkman\Repositories\TagRepository;

/**
 * Serves you by simplifying usage of tags
 */
class Tagservice
{
    private $tags;

    public function __construct(TagRepository $tags)
    {
        $this->tags = $tags;
    }

    /**
     * Finds a Tag object (or creates) then link it to FileContent
     */
    public function tag(FileContent $content, $tagName)
    {
        if ($content->hasTag($tagName)) {
            return;
        }

        $tag = $this->tags->findByName($tagName);

        if ($tag == null) {
            $tag = $this->tags->create($tagName);
        }

        $content->addTag($tag);
    }
}
