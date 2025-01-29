<?php

namespace WildanMZaki\Wize\Commands\Dev;

use WildanMZaki\Wize\Command;

class Clear extends Command
{
    protected $signature = 'dev:clear';
    protected $description = 'Clear development generated resources';

    public function run()
    {
        $directories = [
            'application/controllers',
            'application/models',
            'application/helpers',
            'application/libraries',
            'application/modules'
        ];

        foreach ($directories as $directory) {
            $this->clearDirectory($directory);
            $this->say("Cleared contents of $directory");
        }

        $this->success("All specified directories have been cleared.");
    }

    private function clearDirectory($directory)
    {
        // Check if directory exists
        if (!is_dir($directory)) {
            $this->warning("Directory $directory does not exist.");
            return;
        }

        // Use Recursive Directory Iterator to delete contents
        $iterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
    }
}
