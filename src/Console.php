<?php

namespace WildanMZaki\Wize;

class Console
{
    use Util;
    protected $commands = [];
    protected $scopes = [];
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

    protected function loadCommands($directory)
    {
        if (is_dir($directory)) {
            $iterator = new \DirectoryIterator($directory);
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                    $class = $this->getClassFromFile($fileInfo->getPathname());
                    if ($class && $this->isValidCommandClass($class)) {
                        $this->commands[] = new $class();
                    }
                }
            }
        }
        // else {
        //     $this->danger("Can't find command class '{$this->commandClass}' in '{$this->scopesNamespace()}' scope namespace");
        //     $this->end();
        // }
    }

    protected function getClassFromFile($file)
    {
        // Define the base namespace for commands
        $baseNamespace = $this->baseNamespace;

        // Determine the scope namespace
        $scopeNamespace = $this->scopesNamespace();

        // Construct the fully qualified class name
        $class = basename($file, '.php');
        $fullClassName = $baseNamespace . $scopeNamespace . '\\' . $class;

        // Check if the class exists
        return class_exists($fullClassName) ? $fullClassName : null;
    }

    protected function scopesNamespace(): string
    {
        $result = '';
        if (!empty($this->scopes)) {
            foreach ($this->scopes as $scope) {
                $result .= "\\{$this->pascalize($scope)}";
            }
        }

        return $result;
    }

    protected function isValidCommandClass($class)
    {
        return is_subclass_of($class, Command::class);
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
        $custom_commands = (BASE_PATH . '/' . $this->configs['extend'] . '/Commands');
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
        // $directory = __DIR__ . '/Commands';
        foreach ($sources as $source) {
            $this->baseNamespace = $source->namespace;
            $directory = $source->directory;

            // Minimize the load command process by checking scoped command
            if (!empty($scopes)) {
                foreach ($scopes as $scope) {
                    $directory .= "/{$this->pascalize($scope)}";
                }
            }

            // Load commands from the determined directory
            $this->loadCommands($directory);

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
        }

        // Command not found, list available commands
        $this->danger("Command not found: '$cmd'");
        $this->ln();
        $this->listCommands();
    }
}
