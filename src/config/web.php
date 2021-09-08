<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$routes = require __DIR__ . '/routes.php';
$config = [
    'id' => 'ips-elasticsearch',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'sByvFZRCjRLQGKOyaJVI6YDQrCFlxGRv',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => getenv('REDIS_HOST'),
                'port' => getenv('REDIS_PORT'),
                'database' => 0,
            ]
        ],
        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => getenv('ELASTIC_HOST')],
            ],
            // set autodetectCluster to false if you don't want to auto detect nodes
            // 'autodetectCluster' => false,
            'dslVersion' => 5, // default is 5
        ],
        'elasticsearch_color' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => getenv('ELASTIC_COLOR_HOST')],
            ],
        ],
        'elasticsearch_second' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => getenv('ELASTIC_SECOND_HOST')],
            ],
        ],
        'elasticsearch_search_keyword' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => getenv('ELASTIC_KEYWORD_HOST')],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'info', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => $routes,

        'redis_search' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6an0ba8tty6e8v03.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 0,
            'password' => getenv('REDIS_PASSWORD'),
        ],

        'redis_monitor' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_SEARCH_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE2'),
            'password' => getenv('REDIS_PASSWORD'),
        ],

        'redis0' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE0'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE2'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis2' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE2'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis4' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE4'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis5' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE5'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis6' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE6'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis7' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE7'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis8' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE8'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
        'redis9' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOSTNAME'),
            'port' => 6379,
            'database' => getenv('REDIS_DATABASE9'),
            'password' => getenv('REDIS_PASSWORD'),
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.154.*', '192.168.18.*', '192.168.239.*', '192.168.159.*'],
    ];
}

return $config;
