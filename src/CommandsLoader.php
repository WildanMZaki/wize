<?php

namespace WildanMZaki\Wize;

trait CommandsLoader
{
    protected $scopes = []; // Stores the scope for namespace building

    protected function loadCommandsFromSources(array $sources, &$commands)
    {
        foreach ($sources as $source) {
            $namespace = $source->namespace;
            $directory = $source->directory;

            if (is_dir($directory)) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        // Build class namespace with scopes included
                        $class = $this->getClassFromFile($file->getPathname(), $namespace);
                        if ($class && $this->isValidCommandClass($class)) {
                            $commands[] = new $class();
                        }
                    }
                }
            }
        }
    }

    protected function getClassFromFile($file, $baseNamespace)
    {
        // Determine scope-based namespace
        $scopeNamespace = $this->scopesNamespace();

        // Generate the relative class name from file path
        $relativePath = str_replace([__DIR__, '/', '.php'], ['', '\\', ''], $file);
        $class = basename($relativePath);

        // Combine base namespace, scope namespace, and class name
        $fullClassName = $baseNamespace . $scopeNamespace . '\\' . $class;

        return class_exists($fullClassName) ? $fullClassName : null;
    }

    protected function scopesNamespace(): string
    {
        $result = '';
        if (!empty($this->scopes)) {
            foreach ($this->scopes as $scope) {
                $result .= "\\" . $this->pascalize($scope);
            }
        }
        return $result;
    }

    protected function isValidCommandClass($class)
    {
        return is_subclass_of($class, Command::class);
    }
}
