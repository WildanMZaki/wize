<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command as WizeCommand;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Command extends WizeCommand
{
    protected $signature = 'create:command {name}
        {--desc= : Define description of the command on creation}
    ';
    protected $description = 'Help you create your custom command';

    public function run()
    {
        $command = $this->argument('name');
        $extend_dir = $this->config('extend');
        $path = BASE_PATH . '/' . $extend_dir . "/Commands";
        $command = str_replace('/', ':', $command);
        $command = str_replace('\\', ':', $command);
        $parts = explode(':', $command);

        $baseNamespace = "WildanMZaki\Wize\Extend\Commands";

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
                $command_file = "$path.php";
                File::create($command_file, $content);
                $this->success("Command: [$command_file] created successfully");
            }
        }
    }
}
