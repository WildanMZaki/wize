<?php

namespace WildanMZaki\Wize\Commands\Dev;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class NewCommand extends Command
{
    protected $signature = 'dev:new-command {name} --desc=';
    protected $description = 'This command can help you create other command';

    public function run()
    {
        $command = $this->argument('name');
        $path = __DIR__ . "/..";
        $command = str_replace('/', ':', $command);
        $command = str_replace('\\', ':', $command);
        $parts = explode(':', $command);

        $baseNamespace = "WildanMZaki\Wize\Commands";

        foreach ($parts as $i => $part) {
            $word = $this->pascalize($part);
            $path .= "/$word";
            if ($i !== (count($parts) - 1)) {
                if (!is_dir($path)) {
                    mkdir($path);
                    $this->inform("Creating directory: $path");
                }
                $baseNamespace .= "\\$word";
            } else {
                $content = Template::replace([
                    'namespace' => $baseNamespace,
                    'name' => $word,
                    'command' => $command,
                    'description' => $this->option('desc') ?? '',
                ])->get('command');
                File::create("$path.php", $content);
                $this->success("Creating Command: $path");
            }
        }
        $this->end();
    }
}
