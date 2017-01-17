<?php

namespace Linkman\Plugin\Output;

use Linkman\Plugin\ContentOutputInterface;
use Traversable;

class Json implements ContentOutputInterface
{
    public function getName()
    {
        return 'json';
    }

    public function getDescription()
    {
        return 'Sends json contents';
    }

    public function execute(Traversable $contents, $argValue)
    {
        $data = [];

        foreach ($contents as $content) {
            foreach ($content->getFiles() as $file) {
                $data[] = [
                    'mount' => $file->getMount()->getName(),
                    'contentId' => $content->getId(),
                    'fileId' => $file->getId(),
                    'filePath' => $file->getPath(),
                    'modified' => $content->getModifiedAt()->format('d.m.y H:i')
                ];
            }
        }

        echo json_encode($data);
    }
}
