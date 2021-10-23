<?php

return [
    'id' => 'test',
    'basePath' => dirname(dirname(__DIR__)),
    'components' => [
        'db' => require __DIR__ . '/db.php'
    ],
];
