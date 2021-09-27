<?php

use app\components\JsonStreamTarget;

return  [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => \yii\log\FileTarget::class,
            'levels' => [],
        ],
        [
            'class' => JsonStreamTarget::class,
            'url' => 'php://stdout',
            'levels' => ['info', 'trace'],
            'logVars' => [],
        ],
        [
            'class' => JsonStreamTarget::class,
            'url' => 'php://stderr',
            'levels' => ['error', 'warning'],
            'logVars' => [],
        ],
    ],
];
