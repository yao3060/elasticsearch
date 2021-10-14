<?php
$class ='yii\redis\Connection';
return [
    'redis_search' => [
        'class' => $class,
        'hostname' => getenv('REDIS_SEARCH_HOSTNAME'),
        'port' => 6379,
        'database' => 0,
        'password' => getenv('REDIS_SEARCH_PASSWORD'),
    ],

    'redis_monitor' => [
        'class' => $class,
        'hostname' => getenv('REDIS_SEARCH_HOSTNAME'),
        'port' => 6379,
        'database' => getenv('REDIS_DATABASE2'),
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis0' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 0,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 2,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis2' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 2,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis4' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 4,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis5' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 5,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis6' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 6,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis7' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 7,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis8' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 8,
        'password' => getenv('REDIS_PASSWORD'),
    ],
    'redis9' => [
        'class' => $class,
        'hostname' => getenv('REDIS_HOSTNAME'),
        'port' => 6379,
        'database' => 9,
        'password' => getenv('REDIS_PASSWORD'),
    ],
];
