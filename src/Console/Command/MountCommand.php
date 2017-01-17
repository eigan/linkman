<?php

namespace Linkman\Console\Command;

use Exception;

use Linkman\Console\LinkmanCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class MountCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('mount');
        $this->addArgument('TARGET', InputArgument::REQUIRED, 'Mount target');
        $this->addArgument('NAME', InputArgument::REQUIRED, 'Mount name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = realpath($input->getArgument('TARGET'));
        $name = $input->getArgument('NAME');

        if (file_exists($target) == false) {
            throw new Exception("Target ($target) doesnt exist");
        }

        $linkman = $this->linkman;

        $linkman->mount($target, $name);
    }
}
