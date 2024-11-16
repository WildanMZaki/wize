<?php

namespace WildanMZaki\Wize\Commands\Create;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;
use WildanMZaki\Wize\Template;

class Migration extends Command
{
    protected $signature = 'create:migration {name}
        {--desc= : Migration description if required}
    ';
    protected $description = 'Help you easily create sql file for migration purpose';

    public function run()
    {
        $database_path = $this->config('paths.database');
        $directory = $this->unifyPath(_rootz("$database_path/migrations"));

        $name = $this->argument('name');
        if (!$name) {
            $name = $this->ask('What is your migration name?');
        }
        $name = str_replace(' ', '_', $name);

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.sql";

        $path = $directory . DIRECTORY_SEPARATOR . $fileName;

        if (File::exists($path)) {
            $this->danger("Unexpectedly file [$path] already exists");
            $this->end();
        }

        $content = Template::replace([
            'name' => $fileName,
            'now' => date('Y-m-d H:i:s'),
        ])->get('migration');

        $description = $this->option('desc');
        if ($description) {
            $content .= "\n";
            $content .= Template::replace([
                'description' => $description,
            ])->get('migration_description');
            $content .= "\n";
        }

        $this->ensureDirectory($path);
        $result = File::create($path, $content);
        if ($result) {
            $this->success("Migration [$path] created successfully");
        }
    }
}
