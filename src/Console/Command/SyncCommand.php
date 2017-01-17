<?php

namespace Linkman\Console\Command;

use Linkman\Console\LinkmanCommand;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('sync');
        $this->addArgument('path', InputArgument::OPTIONAL, null, '');
        $this->addOption('--force', '-f', InputOption::VALUE_OPTIONAL, 'Force sync of every content?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $linkman = $this->linkman;
        $numFiles = 0;

        foreach ($linkman->api()->mounts() as $i => $mount) {
            $numFiles += $linkman->filesystem($mount)->countFiles();
        }

        $progress = new ProgressBar($output, $numFiles);
        $progress->start();

        $linkman->hook('sync.file.start', function () use ($progress) {
            $progress->advance();
        });

        $progress->setRedrawFrequency(10);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $progress->setBarCharacter('<fg=magenta>=</>');
        $progress->setProgressCharacter('>');

        foreach ($linkman->api()->mounts() as $i => $mount) {
            $linkman->syncMount($mount, null, $input->hasParameterOption('--force'));
        }

        $progress->finish();

        $output->writeln('Done');
    }
}
