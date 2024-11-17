<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Composer;
use WildanMZaki\Wize\Template;

class Info extends Command
{
    protected $signature = 'info';
    protected $description = 'Tool information';

    public function run()
    {
        $composerData = Composer::extract();
        if (!is_array($composerData)) {
            return 'dev-main'; // Fallback if parsing fails
        }

        $version = $composerData['version'] ?? 'dev-main';
        $last_update = '2024-11-17 23:59:59';
        if (isset($composerData['extra']['last-update'])) {
            $last_update = $composerData['extra']['last-update'];
        }
        $description = 'This is php cli tool specialized for codeigniter 3 development helper';
        if (isset($composerData['description'])) {
            $description = $composerData['description'];
        }
        $caller = $this->caller;
        $replacer = compact('version', 'last_update', 'description', 'caller');
        $content = Template::replace($replacer)->get('info');

        $this->display($content);

        $author = new Author();
        $author->run();

        // List available command
        $list = new _List();
        $list->run();
    }
}
