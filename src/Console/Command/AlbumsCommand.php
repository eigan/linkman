<?php

namespace Linkman\Console\Command;

use Linkman\Console\LinkmanCommand;

use Symfony\Component\Console\Helper\Table;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class AlbumsCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('albums');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $albums = $this->linkman->api()->albums();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Album', 'Num contents']);

        foreach ($albums as $album) {
            $table->addRow([$album->getId(), $album->getTitle(), count($album->getContents())]);
        }

        $table->render();
    }
}
