<?php

namespace Linkman\Console\Command;

use Linkman\Console\LinkmanCommand;

use Symfony\Component\Console\Helper\Table;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HooksCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('hooks');
        $this->setDescription('List all connected hooks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);

        $table->setHeaders(['Hook', 'Num callbacks']);

        foreach ($this->linkman->getHooks() as $hookName => $callbacks) {
            $table->addRow([$hookName, count($callbacks)]);
        }

        $table->render();
    }
}
