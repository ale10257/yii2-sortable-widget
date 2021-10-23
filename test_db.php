#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (\Exception $e) {
}
$db = __DIR__.'/test-db.sql';

echo `PGPASSWORD={$_ENV['POSTGRES_PASSWORD']} psql -h {$_ENV['POSTGRES_HOST']} -d {$_ENV['POSTGRES_DB']} -U {$_ENV['POSTGRES_USER']} -f {$db}`;