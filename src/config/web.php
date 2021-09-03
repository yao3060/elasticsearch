<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
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
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
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
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'PUT  v1/logs/<id:\d+>' => 'log/update',
                'POST v1/logs' => 'log/create',
                'GET  v1/logs' => 'log/index',

            ],
        ],
        'session' => [
            'name'=>'IPSSESSION',
            'class' => 'yii\redis\Session',
            'redis' => [
                'hostname' => 'r-uf6sl57iwn5z61ssjo.redis.rds.aliyuncs.com',
                'port' => 6379,
                'database' => 0,
                'password'=>'Er019Tgsdtwcs0fDs',
            ],
            'timeout' => 3600*24,
            'cookieParams' => [
                "domain"=>".zzy_web.com",
                'httpOnly' => false,
                'lifetime' => 3600*24*7,
            ]
        ],
        'redis0' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 0,
            'password'=>'Er019Tgsdtwcs0fDs',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 2,
            'password'=>'Er019Tgsdtwcs0fDs',
        ],
        'redis2' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 2,
            'password'=>'Er019Tgsdtwcs0fDs',
        ],
        'redis4' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 4,
            'password'=>'Er019Tgsdtwcs0fDs',
        ],
        'redis5' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 5,
            'password'=>'Er019Tgsdtwcs0fDs',
        ],
        'redis6' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 6,
            'password'=>'Er019Tgsdtwcs0fDs',
        ],
        'redis7' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 7,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'redis8' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 8,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'redis9' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6ho5b75zcknf33lr.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 9,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'redis_qiantu1' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6076tx6mj1wyh4pj.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 1,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'redis_search' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6an0ba8tty6e8v03.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 0,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'redis_templ' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6an0ba8tty6e8v03.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 1,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'redis_monitor' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6an0ba8tty6e8v03.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 2,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'user_redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf6sl57iwn5z61ssjo.redis.rds.aliyuncs.com',
            'port' => 6379,
            'database' => 1,
            'password' => 'Er019Tgsdtwcs0fDs',
        ],
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://root:2g5#Ymlvqo0g@dds-uf6d2487b504f5741.mongodb.rds.aliyuncs.com:3717,dds-uf6d2487b504f5742.mongodb.rds.aliyuncs.com:3717/admin?replicaSet=mgset-15646223',
        ],
        'mongodb_log' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://root:2g5#Ymlvqo0g@dds-uf69542aa4e292c41.mongodb.rds.aliyuncs.com:3717,dds-uf69542aa4e292c42.mongodb.rds.aliyuncs.com:3717/admin?replicaSet=mgset-50994405',
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
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
