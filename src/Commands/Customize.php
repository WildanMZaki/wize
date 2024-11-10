<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Config;
use WildanMZaki\Wize\File;

class Customize extends Command
{
    protected $signature = 'customize {-y: Yes to all questions}';
    protected $description = 'Initiate to customize the library';

    public function run()
    {
        // Memastikan nama folder yang mau user gunakan sebagai tempat penyimpanan custom
        $folder_name = $this->config('extend');
        $default = $folder_name;

        if (!$this->option('y')) {
            $folder_name = $this->ask('Folder name that you want to use?', $folder_name);
            if ($default !== $folder_name) Config::set('extend', (string) $folder_name);
        }

        $custom_directory = _rootz($folder_name);
        if (is_dir($custom_directory)) {
            $this->danger("Custom directory already exists: [$custom_directory]");
            $this->end();
        }
        $this->ensureDirectory($custom_directory, false);
        $this->ensureDirectory("$custom_directory/Commands", false);
        $this->ensureDirectory("$custom_directory/templates", false);

        if ($this->option('y')) {
            $confirm = 'y';
        } else {
            $this->ln();
            $confirm = $this->ask("This operation will make a small modification to your composer.json file to enable autoloading for custom commands. Do you want to proceed?", 'Y');
        }
        if (strtolower($confirm) === 'y' || strtolower($confirm) === 'yes') {
            // Set up composer psr-4 autoload
            $composerFile = _rootz('composer.json');

            // Check if composer.json exists
            if (!File::check($composerFile)) {
                $this->warning("composer.json not found. Skipping autoload setup.");
                return;
            }

            $composerConfig = File::parseJSON()->assoc()->get($composerFile);
            if (!$composerConfig) {
                $this->warning("Could not read composer.json. Skipping autoload setup.");
                return;
            }

            $composerConfig['autoload']['psr-4']['WildanMZaki\\Wize\\Extend\\'] = "$folder_name/";
            File::create($composerFile, json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Show a timer during the `composer dump-autoload` process
            $start = time();

            // Execute composer dump-autoload with proc_open
            $process = proc_open(
                'composer dump-autoload',
                [
                    ['pipe', 'r'], // STDIN
                    ['pipe', 'w'], // STDOUT
                    ['pipe', 'w'], // STDERR
                ],
                $pipes
            );

            if (is_resource($process)) {
                // Monitor the elapsed time while waiting for the command to complete
                while (proc_get_status($process)['running']) {
                    $elapsed = time() - $start;
                    $this->flash("Running `composer dump-autoload`... (elapsed time: {$elapsed}s)");
                    usleep(500000); // Update every half-second
                }

                // Close pipes and process
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                $this->ln();
                $this->ln();
                $this->success('Customization setup done successfully');
            } else {
                $this->danger('Failed to execute `composer dump-autoload`.');
            }
        }
    }
}
