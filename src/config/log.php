<?php

use app\components\JsonStreamTarget;

return  [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => \yii\log\FileTarget::class,
            'levels' => ['error', 'warning', 'info'],
        ],
        [
            'class' => JsonStreamTarget::class,
            'url' => 'php://stdout',
            'levels' => [],
            'logVars' => [],
        ],
        [
            'class' => JsonStreamTarget::class,
            'url' => 'php://stderr',
            'levels' => [],
            'logVars' => [],
        ],
    ],
];
