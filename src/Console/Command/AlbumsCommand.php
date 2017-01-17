<?php

namespace Linkman\Console\Command;

use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class AlbumsCommand extends Command
{
    protected function configure()
    {
        $this->setName('albums');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
