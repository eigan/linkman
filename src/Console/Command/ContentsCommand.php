<?php

namespace Linkman\Console\Command;

use Exception;
use Linkman\Console\Input\PluginInputOption;

use Linkman\Console\LinkmanCommand;

use Linkman\Plugin\ContentActionInterface;
use Linkman\Plugin\ContentOutputInterface;
use RuntimeException;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class ContentsCommand extends LinkmanCommand
{
    protected function configure()
    {
        $this->setName('contents');

        $inputDefinition= new InputDefinition();

        foreach ($this->linkman->modifiers() as $modifier) {
            $inputDefinition->addOption(new PluginInputOption($modifier->getName(), $modifier));
        }

        foreach ($this->linkman->callables(ContentActionInterface::class) as $action) {
            $inputDefinition->addOption(new PluginInputOption('action-' . $action->getName(), $action));
        }

        foreach ($this->linkman->callables(ContentOutputInterface::class) as $output) {
            $inputDefinition->addOption(new PluginInputOption('output-'.$output->getName(), $output));
        }

        $inputOptions = $inputDefinition->getOptions();

        usort($inputOptions, function ($current, $next) {
            return $current->getName() > $next->getName();
        });

        $inputDefinition->setOptions($inputOptions);

        $inputDefinition->addArgument(new InputArgument('path', InputArgument::OPTIONAL, 'Only contents in this path'));

        $this->setDefinition($inputDefinition);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();

        $path = $input->getArgument('path');

        if ($path) {
            $path = realpath($path);
        }

        $filters = $actions = $outputs = [];
        foreach ($options as $option => $argValue) {
            if ($input->getParameterOption('--'.$option) === false) {
                continue;
            }

            $pluginInput = $this->getDefinition()->getOption($option);

            if (strpos($option, 'filter') === 0) {
                $filters[$option] = $argValue;
            }

            if (strpos($option, 'action') === 0) {
                $actions[] = ['name' => substr($option, 6), 'argValue' => $argValue, 'option' => $pluginInput];
            }

            if (strpos($option, 'output') === 0) {
                $outputs[] = ['name' => substr($option, 7), 'argValue' => $argValue, 'option' => $pluginInput];
            }
        }

        if (empty($outputs) && empty($actions)) {
            throw new RuntimeException('No outputs or actions specified, please choose');
        }

        $contents = $this->linkman->api()->contents($filters, [
            'path' => $path
        ]);

        try {
            foreach ($actions as $action) {
                $action['option']->getCallable()->execute($contents, $action['argValue']);
            }
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
        }

        $this->linkman->flush();

        foreach ($outputs as $outputOption) {
            $outputOption['option']->getCallable()->execute($contents, $outputOption['argValue']);
        }
    }
}
