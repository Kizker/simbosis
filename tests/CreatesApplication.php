<?php

namespace Tests;

use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        if ((getenv('APP_ENV') ?: '') === 'testing' && (getenv('DB_CONNECTION') ?: '') === 'mysql') {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $database = getenv('DB_DATABASE') ?: 'fullpower_news_test';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD');

            $pdo = new \PDO("mysql:host={$host};port={$port}", $username, $password ?: '');
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }
}
