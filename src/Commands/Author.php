<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Composer;

class Author extends Command
{
    protected $signature = 'author';
    protected $description = "Display tool's author(s) and contributor(s)";

    public function run()
    {
        $composerData = Composer::extract();
        if (!$composerData) {
            $this->danger('composer.json not found or invalid.');
            return;
        }

        $authors = $composerData['authors'] ?? [];
        $contributors = $composerData['extra']['contributors'] ?? [];

        $this->say($this->colorize('Author(s) & Contributor(s):', 'yellow'));

        // Display authors
        if (!empty($authors)) {
            $this->say($this->colorize('Main Author(s):', 'green'));
            foreach ($authors as $author) {
                $name = $author['name'] ?? 'Unknown';
                $email = $author['email'] ?? '';
                $this->say(" - $name" . ($email ? " <$email>" : ''));
            }
        } else {
            $this->inform('No authors found in composer.json.');
        }

        $this->ln();

        // Display contributors
        if (!empty($contributors)) {
            $this->say($this->colorize('Contributor(s):', 'cyan'));
            foreach ($contributors as $contributor) {
                $name = $contributor['name'] ?? 'Unknown';
                $email = $contributor['email'] ?? '';
                $this->say(" - $name" . ($email ? " <$email>" : ''));
            }
        }
    }
}
