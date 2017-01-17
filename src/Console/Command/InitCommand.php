<?php

namespace Linkman\Console\Command;

use Exception;

use Linkman\Exception\AlreadyInitializedException;

use Linkman\Exception\NotInitializedException;
use Linkman\Linkman;

use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Initialize Linkman in this directory');
        $this->addArgument('path', InputArgument::OPTIONAL, 'Path to where you want to store linkman.db and mounts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $linkman = $this->getApplication()->getLinkman();
        } catch (NotInitializedException $e) {
            // Ok, we ready
            $linkman = $e->getLinkman();
        } catch (Exception $e) {
            $output->writeln('Linkman already exists');
            $output->writeln($e->getMessage());
            return 1;
        }

        $currentPath = realpath(getcwd());

        $path = realpath($input->getArgument('path'));

        if ($path == null) {
            $path = $currentPath;
        }

        if ($path != null && is_writeable(realpath($path)) == false) {
            $output->writeln('The path specified is not writeable');
            return;
        }

        $output->writeln("Initializing Linkman in $path");

        try {
            $linkman->initialize();
        } catch (AlreadyInitializedException $e) {
            $output->writeln('Linkman is already initialized');
        }
    }
}
