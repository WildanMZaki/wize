<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Composer;
use WildanMZaki\Wize\Template;

class Version extends Command
{
    protected $signature = 'version';
    protected $description = 'Tool version';

    public function run()
    {
        $version = $this->extractComposerVersion();
        $content = Template::replace(compact('version'))->get('version');

        $this->display($content);
    }

    protected function extractComposerVersion(): string
    {
        $composerData = Composer::extract();
        if (!is_array($composerData)) {
            return 'dev-main'; // Fallback if parsing fails
        }

        $version = $composerData['version'] ?? 'dev-main';
        return $version;
    }
}
