<?php

namespace WildanMZaki\Wize;

class Console
{
    use Util, CommandsLoader;
    protected $commands = [];
    protected $commandClass = '';

    protected $configs = [];
    protected string $caller = 'wize';

    protected string $baseNamespace;

    public function __construct()
    {
        $this->configs = Config::extract();
    }

    public function setConfig(array $configs): void
    {
        $this->configs = [...$this->configs, ...$configs];
    }

    public function setCaller(string $filename): void
    {
        $this->caller = $filename;
    }

    public function listCommands()
    {
        $this->say('Available Commands:');
        foreach ($this->commands as $command) {
            $cmd = $this->colorize($command->cmd(), 'yellow');
            $this->say($cmd . " : " . $command->desc());
        }
        $this->end();
    }

    public function run(string $cmd = '', $args = [])
    {
        $this->ln();
        if (!$cmd) {
            $this->danger('Command must be defined');
            $this->ln();
            $this->say("Example: php {$this->caller} [command]");
            $this->end();
        }

        // Check if the command is scoped (e.g., create:helper, create:controller)
        $scopes = explode(':', $cmd);
        $this->commandClass = $this->pascalize(array_pop($scopes));

        $this->scopes = $scopes;

        // Determine the sources to load commands from
        $custom_commands = _rootz("{$this->configs['extend']}/Commands");
        $sources = [
            (object) [
                'namespace' => 'WildanMZaki\\Wize\\Extend\\Commands',
                'directory' => $custom_commands,
            ],
            (object) [
                'namespace' => 'WildanMZaki\\Wize\\Commands',
                'directory' => (__DIR__ . '/Commands'), // Default Commands
            ],
        ];

        $this->loadCommandsFromSources($sources, $this->commands);

        // Execute the command
        foreach ($this->commands as $command) {
            if ($command->cmd() === $cmd) {
                $command->setCaller($this->caller);
                $command->setConfigs($this->configs);
                $command->setArgsAndOptions($args);
                $command->run();
                return;
            }
        }

        // Command not found, list available commands
        $this->danger("Command not found: '$cmd'");
        $this->ln();
        $this->listCommands();
    }
}
