<?php

return (function () {

    $params = require __DIR__ . '/params.php';
    $log = require __DIR__ . '/log.php';
    $db = require __DIR__ . '/db.php';
    $routes = require __DIR__ . '/routes.php';
    $redis = require __DIR__ . '/redis.php';
    $elasticsearch = require __DIR__ . '/elasticsearch.php';

    $components = array_merge(
        [
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
            'log' => $log,
            'urlManager' => $routes,
        ],
        $redis,
        $elasticsearch,
        $db
    );

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
        'components' => $components,
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
})();
