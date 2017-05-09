<?php

namespace Linkman\Console;

use Linkman\Console\Command\AlbumsCommand;
use Linkman\Console\Command\ContentsCommand;

use Linkman\Console\Command\HooksCommand;

use Linkman\Console\Command\InitCommand;

use Linkman\Console\Command\MountCommand;
use Linkman\Console\Command\ServeCommand;
use Linkman\Console\Command\MountsCommand;

use Linkman\Console\Command\SyncCommand;
use Linkman\Exception\NotInitializedException;

use Linkman\Linkman;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\ArgvInput;

use Symfony\Component\Console\Input\InputOption;

/**
 * TODO: Handle when calling commands and have not initialized
 */
class Console extends Application
{
    public function __construct($path)
    {
        $this->path = $path;

        try {
            $this->linkman = $this->getLinkman();
        } catch (NotInitializedException $e) {
            $this->linkman = null;
        }

        parent::__construct('Linkman CLI', Linkman::VERSION);
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[] An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $commands = [
            new InitCommand(),
            new HelpCommand(),
            new ListCommand()
        ];

        if ($this->linkman) {
            $commands[] = new AlbumsCommand($this->linkman);
            $commands[] = new MountsCommand($this->linkman);
            $commands[] = new HooksCommand($this->linkman);
            $commands[] = new ContentsCommand($this->linkman);
            $commands[] = new MountCommand($this->linkman);
            $commands[] = new SyncCommand($this->linkman);
            $commands[] = new ServeCommand($this->linkman);
        }

        return $commands;
    }

    public function getLinkman() : Linkman
    {
        return new Linkman($this->getLinkmanPath());
    }

    protected function getLinkmanPath()
    {
        $input = new ArgvInput();

        $path = $input->getParameterOption('--path');

        if ($input->hasParameterOption('--path') === false) {
            $path = $this->getLinkmanPathOption()->getDefault();
        }

        if (function_exists('posix_getuid') && strpos($path, '~') !== false) {
            $info = posix_getpwuid(posix_getuid());
            $path = str_replace('~', $info['dir'], $path);
        }

        if (file_exists($path)) {
            return $path;
        }

        throw new \InvalidArgumentException("Invalid path: [$path]");
    }

    /**
     * Get the default input definitions for the applications.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption($this->getLinkmanPathOption());

        return $definition;
    }

    /**
     * Get the global tenant option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    private function getLinkmanPathOption()
    {
        $message = 'The path to the linkman.db';

        return new InputOption('path', null, InputOption::VALUE_OPTIONAL, $message, './');
    }
}
