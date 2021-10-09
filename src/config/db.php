<?php

return [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
        'username' => getenv('DB_USERNAME'),
        'password' => getenv('DB_PASSWORD'),
        'charset' => 'utf8',

        // Schema cache options (for production environment)
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 86400,
        'schemaCache' => 'db_cache',
    ],
    'backend_db' => [
        'class' => '\yii\db\Connection',
        'dsn' => 'mysql:host=' . getenv('BACKEND_DB_HOST') . ';dbname=' . getenv('BACKEND_DB_DATABASE'),
        'username' => getenv('BACKEND_DB_USERNAME'),
        'password' => getenv('BACKEND_DB_PASSWORD'),
        'charset' => 'utf8',
        // Schema cache options (for production environment)
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 86400,
        'schemaCache' => 'backend_db_cache',
    ],
];
