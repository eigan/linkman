<?php

namespace Linkman\Console\Command;

use Linkman\Console\LinkmanCommand;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('serve');
		$this->setDescription('Serve this site via the builtin PHP webserver');
		$this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost');
		$this->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8080);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $approot = __DIR__.'/../../..';

        $php = escapeshellarg(PHP_BINARY);
		$docroot = escapeshellarg(realpath("$approot/public"));
		$server = escapeshellarg(realpath("$approot/public/api.php"));
		$host = $input->getOption('host');
		$port = $input->getOption('port');
		$output->writeln("Starting PHP server at $host:$port");
		passthru("$php -S $host:$port -t $docroot $server");
    }
}
