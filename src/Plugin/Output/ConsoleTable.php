<?php

namespace Linkman\Plugin\Output;

use Linkman\Plugin\ContentOutputInterface;
use Symfony\Component\Console\Helper\Table;

use Symfony\Component\Console\Output\ConsoleOutput;

use Traversable;

class ConsoleTable implements ContentOutputInterface
{
    public function getName()
    {
        return 'table';
    }

    public function getDescription()
    {
        return 'Basic table with info';
    }

    public function execute(Traversable $contents, $argValue)
    {
        $output = new ConsoleOutput();

        if (empty($contents)) {
            $output->writeln('No matches');
            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Mount', 'ContentID', 'FileID', 'Path', 'Modified', 'Tags']);

        foreach ($contents as $content) {
            foreach ($content->getFiles() as $file) {
                $tags = $this->makeTagList($file);
                $table->addRow([$file->getMount()->getName(), $content->getId(), $file->getId(), $file->getPath(), $content->getModifiedAt()->format('d.m.y H:i'), $tags]);
            }
        }
        $table->render();
    }

    public function makeTagList($file)
    {
        $tags = $file->getContent()->getTags();

        $tagNames = [];

        foreach ($tags as $tag) {
            $tagNames[] = $tag->getDisplayName();
        }

        return implode(', ', $tagNames);
    }
}
