<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Library extends Command
{
    protected $signature = 'create:library {name} {--theme=default : Theme is template you want to use}';
    protected $description = 'This command can help you create library';

    public function run()
    {
        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your library name?');
        }

        $name = $this->pascalize($name);

        $path = _libraries("$name.php");
        if (File::exists($path)) {
            $this->danger("Library '$name' already exists");
            $this->end();
        }

        $content = Template::theme($this->option('theme'))->replace([
            'name' => $name,
        ])->get('library');

        $result = File::create($path, $content);
        if ($result !== false) {
            $this->success("Library '$name' created successfully");
            $this->end();
        }
    }
}
