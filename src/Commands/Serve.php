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

        $docRoot = rtrim(_rootz($this->config('paths.root')), DIRECTORY_SEPARATOR);

        // Command to start PHP's built-in server
        $command = sprintf(
            'php -S %s:%s -t %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($docRoot),
        );

        $this->inform("Starting server on [http://$host:$port]");
        $this->ln();

        passthru($command);
    }
}
