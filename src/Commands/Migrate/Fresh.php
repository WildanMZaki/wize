<?php

namespace WildanMZaki\Wize\Commands\Migrate;

use WildanMZaki\Wize\Command;
use WildanMZaki\Wize\Commands\Migrate;

class Fresh extends Command
{
    protected $signature = 'migrate:fresh
        {--conn=default : Database connection that you want to choose}
        {-f : Force running in any environment}
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

        $defaultConnection = $this->config('migration.connection') ?? 'default';
        $optionConnection = $this->option('conn');

        $connection = ($optionConnection && $optionConnection !== 'default') ? $optionConnection : $defaultConnection;

        try {
            $this->conn = $this->ci->load->database($connection, TRUE);
        } catch (\Exception $e) {
            $this->danger("Error loading database connection [$connection]: " . $e->getMessage());
            $this->end();
        }

        $this->migration_table = $this->config('migration.table');

        $this->inform("Dropping all tables");

        try {
            $this->conn->query('SET FOREIGN_KEY_CHECKS=0;');
            $tables = $this->conn->list_tables();

            foreach ($tables as $table) {
                if ($table === $this->migration_table) {
                    continue;
                }

                // $this->justify("Dropping table: $table", '');
                // $this->conn->query("DROP TABLE IF EXISTS `$table`;");
                $this->dynamicAction("Dropping table: $table", function () use ($table) {
                    $this->conn->query("DROP TABLE IF EXISTS `$table`;");
                });
            }

            // Truncate the migration table
            $this->conn->query("TRUNCATE TABLE `$this->migration_table`;");

            // Re-enable foreign key checks
            $this->conn->query('SET FOREIGN_KEY_CHECKS=1;');

            $this->ln();
            // Remigrate
            $migrate = new Migrate();
            $migrate->setConfigs($this->configs);
            $migrate->_options = $this->_options;
            $migrate->ci = $this->ci;
            $migrate->run();
        } catch (\Exception $e) {
            $this->danger("Error refreshing database: " . $e->getMessage());
        }
    }
}
