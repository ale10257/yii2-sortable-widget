<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => "pgsql:host={$_ENV['POSTGRES_HOST']};dbname={$_ENV['POSTGRES_DB']}",
    'username' => $_ENV['POSTGRES_USER'],
    'password' => $_ENV['POSTGRES_PASSWORD'],
    'charset' => 'utf8',
    'on afterOpen' => function ($event) {
        $event->sender->createCommand("SET search_path TO {$_ENV['POSTGRES_SCHEMA']};")->execute();
    }
];