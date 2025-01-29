<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\File;

class Init extends Command
{
    protected $signature = 'init --theme=default --reset=false';
    protected $description = 'Initialize wize cli';

    public function run()
    {
        if (!File::exists(_rootz(WIZE_CONFIG)) || $this->option('reset')) {
            if ($this->option('reset')) {
                $confirmed = $this->confirm('Reset available config?');
                if (!$confirmed) {
                    $this->end();
                }
            }
            $this->inform("Initializing Wize CLI...");

            $root = _rootz('/');
            $detectedConfig = [];

            // 1. Detect modules support (application/modules folder)
            $detectedConfig['module'] = is_dir("$root/application/modules");

            // 2. Detect database folder (database/ or db/)
            if (is_dir("$root/database")) {
                $detectedConfig['paths']['database'] = "database";
            } elseif (is_dir("$root/db")) {
                $detectedConfig['paths']['database'] = "db";
            }

            // 3. Paths detection (if exists, use default, otherwise ask user)
            $paths = [
                'root' => '/',
                'application' => 'application',
                'system' => 'system',
                'views' => 'application/views',
            ];

            foreach ($paths as $key => $default) {
                $detectedConfig['paths'][$key] = is_dir("$root/$default") ? $default :
                    $this->ask("Specify path for $key", $default);
            }

            // 4. Theme selection (default: "default")
            $theme = $this->option('theme') ?? 'default';
            $detectedConfig['theme'] = $theme;

            // 5. Migration settings
            $detectedConfig['migration']['connection'] = 'default';
            $detectedConfig['migration']['table'] = 'ci_migrations';

            // 6. ALias
            if (empty($this->config('aliases'))) {
                $detectedConfig['aliases'] = (object)[];
            }

            $finalConfig = array_replace_recursive($this->configs, $detectedConfig);

            $configFile = _rootz(WIZE_CONFIG);
            File::create($configFile, $finalConfig);

            $this->success("Initialization complete!");
        } else {
            $this->warning(WIZE_CONFIG . ' already available!');
            $this->ln();
            $this->inform('Try use --reset option or just modify it with set:config command');
        }
    }
}
