<?php

namespace WildanMZaki\Wize\Commands\Migrate;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Commands\Migrate;

class Fresh extends Command
{
    protected $signature = 'migrate:fresh
        {--conn=default : Database connection that you want to choose}
        {-f : Force running in any environment}
        {--clean : Clean all database and not automatically remigrating the migrations}
    ';
    protected $description = 'Fresh the database then running again the migration from the beginning';

    protected $conn;

    protected $migration_table;

    public function run()
    {
        $env = $this->config('env');
        if (strtolower($env) != 'development' && !$this->option('f')) {
            if (!$this->confirm("You are not in a development environment. Do you want to proceed?", 'n')) {
                return;
            }
        }

        $this->bootstrap_ci();
        $connection = $this->getDBConnection();

        try {
            $this->conn = $this->ci->load->database($connection, TRUE);
        } catch (\Exception $e) {
            $this->danger("Error loading database connection [$connection]: " . $e->getMessage());
            $this->end();
        }

        $this->migration_table = $this->config('migration.table');
        $clean = $this->option('clean');

        try {
            $this->conn->query('SET FOREIGN_KEY_CHECKS=0;');
            $tables = $this->conn->list_tables();

            if (!empty($tables)) {
                $this->inform("Dropping all tables");

                foreach ($tables as $table) {
                    if ($table === $this->migration_table && !$clean) {
                        continue;
                    }

                    // $this->justify("Dropping table: $table", '');
                    // $this->conn->query("DROP TABLE IF EXISTS `$table`;");
                    $this->dynamicAction("Dropping table: " . $this->colorize($table, 'yellow'), function () use ($table) {
                        $this->conn->query("DROP TABLE IF EXISTS `$table`;");
                    });
                }

                // Re-enable foreign key checks
                $this->conn->query('SET FOREIGN_KEY_CHECKS=1;');

                if (!$clean) {
                    // Truncate the migration table
                    $this->conn->query("TRUNCATE TABLE `$this->migration_table`;");

                    // Remigrate
                    $this->ln();
                    $migrate = new Migrate();
                    $migrate->setConfigs($this->configs);
                    $migrate->_options = $this->_options;
                    $migrate->ci = $this->ci;
                    $migrate->run();
                    return;
                }
            } else {
                $this->inform('No tables found');
            }
        } catch (\Exception $e) {
            $this->danger("Error refreshing database: " . $e->getMessage());
        }
    }

    protected function getDBConnection()
    {
        global $argv;

        $defaultConnection = $this->config('migration.connection') ?? 'default';
        $optionConnection = $this->option('conn');

        // Check if --conn exists explicitly in $argv
        $isConnExplicit = false;
        foreach ($argv as $arg) {
            if (strpos($arg, '--conn=') === 0) {
                $isConnExplicit = true;
                break;
            }
        }

        // Use the optionConnection if explicitly passed, otherwise fallback to the default
        return ($isConnExplicit && $optionConnection) ? $optionConnection : $defaultConnection;
    }
}
