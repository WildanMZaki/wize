<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\CommandsLoader;

class _List extends Command
{
    protected $signature = 'list {scope? : Scope is group where you place the commands}
        {--label : Show list with labeled display for each command}
    ';

    protected $description = 'List all the commands that available';

    protected $commands = [];
    protected $scopes = [];

    public function run()
    {
        $scope = $this->argument('scope');

        $sources = [
            (object) [
                'namespace' => 'WildanMZaki\\Wize\\Extend\\Commands',
                'directory' => _rootz("{$this->config('extend')}/Commands"),
                'origin' => _rootz("{$this->config('extend')}/Commands"),
            ],
            (object) [
                'namespace' => 'WildanMZaki\\Wize\\Commands',
                'directory' => __DIR__, // Default Commands
                'origin' => __DIR__, // Default Commands
            ],
        ];

        $scopeDir = null;
        $message = "Listing all available commands:";
        if ($scope) {
            // List commands from the specific scope
            $scope = str_replace(':', '/', $scope);
            $scope = str_replace('\\', '/', $scope);
            $scopeDir = $this->pascalize($scope, '/', '/');
            $message = "Listing commands for scope: $scope";
        }

        $invalidDirs = [];
        foreach ($sources as $source) {
            if ($scopeDir) {
                $source->directory .= "/$scopeDir";
            }

            if (!is_dir($source->directory)) {
                // Menampung kalau terjadi invalid directory
                $invalidDirs[] = $source->directory;
            } else {
                $this->loadCommands($source->directory, $source->namespace, $source->origin);
            }
        }
        if (count($invalidDirs) == count($sources) && $scope) {
            $this->danger("Scope '$scope' does not exist.");
            $this->end();
        }
        $this->inform($message);
        $this->listCommands();
    }

    protected function loadCommands($directory, $namespace, $origin)
    {
        $current = $origin . DIRECTORY_SEPARATOR;
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                $left = substr($path, strlen($current));

                $normalizedLeft = str_replace(DIRECTORY_SEPARATOR, '/', $left);
                $scopes = explode('/', $normalizedLeft);
                array_pop($scopes);
                $this->scopes = $scopes;
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $class = $this->getClassFromFile($file->getPathname(), $namespace);
                    if ($class && $this->isValidCommandClass($class)) {
                        $this->commands[] = new $class();
                    }
                }
            }
        }
    }

    protected function getClassFromFile($file, $namespace)
    {
        // Determine the scope namespace
        $scopeNamespace = $this->scopesNamespace();

        // Construct the fully qualified class name
        $class = basename($file, '.php');
        $fullClassName = $namespace . $scopeNamespace . '\\' . $class;

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
        $this->ln();
        $this->say('Available Commands:');

        $commands = [];
        $max = 0;
        foreach ($this->commands as $command) {
            $cmd = $this->option('label') ? $this->label($command->cmd(), 'blue') : $command->cmd();
            $description = $command->desc() ?? '';
            $max = max($max, strlen($cmd));
            $commands[] = [$cmd, $description, $command->cmd()];
        }

        $max++;
        $scopped = [];
        $unscopped = [];
        $scannedScope = [];

        sort($commands);
        foreach ($commands as [$command, $desc, $cmd]) {
            $item = str_pad($command, $max) . ($desc ? " : $desc" : "");
            $cmdParts = explode(':', $cmd);
            if (count($cmdParts) > 1) {
                if (!in_array($cmdParts[0], $scannedScope)) {
                    $scannedScope[] = $cmdParts[0];
                    $scopped[] = '';
                    $scopped[] = $this->colorize($cmdParts[0], 'yellow');
                }
                $scopped[] = $item;
            } else {
                $unscopped[] = $item;
            }
        }

        foreach (array_merge($unscopped, $scopped) as $item) {
            $this->say($item);
        }
    }
}
