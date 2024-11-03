<?php

namespace WildanMZaki\Wize\Commands;

use WildanMZaki\Wize\Command;

class Serve extends Command
{
    protected $signature = 'serve 
        {--host=localhost : The host address to serve the application on} 
        {--port=8080 : The port to serve the application on}';

    protected $description = 'Serve the CodeIgniter 3 application';

    public function run()
    {
        $host = $this->option('host');
        $port = $this->option('port');

        // Define the command to start PHP's built-in server
        $command = sprintf('php -S %s:%s index.php', escapeshellarg($host), escapeshellarg($port));

        $this->inform("Starting server on http://$host:$port...");
        passthru($command);
    }
}
