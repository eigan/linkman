<?php

namespace Linkman\Console\Command;

use Linkman\Console\LinkmanCommand;

use Symfony\Component\Console\Helper\Table;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class MountsCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('mounts');
        $this->setDescription('List your mounts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mounts = $this->linkman->api()->mounts();
        $table = new Table($output);
        $table->setHeaders(['Mount', 'ContentID', 'FileID', 'Path', 'Modified', 'Tags']);

        foreach ($mounts as $mount) {
            $table->addRow([$mount->getId(), $mount->getName(), json_encode($mount->getConfig())]);
        }

        $table->render();
    }
}
