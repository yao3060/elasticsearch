<?php

return  [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => \yii\log\FileTarget::class,
            'levels' => [],
        ],
        [
            'class' => \codemix\streamlog\Target::class,
            'url' => 'php://stdout',
            'levels' => ['info', 'trace'],
            'logVars' => [],
        ],
        [
            'class' => \codemix\streamlog\Target::class,
            'url' => 'php://stderr',
            'levels' => ['error', 'warning'],
            'logVars' => [],
        ],
    ],
];
