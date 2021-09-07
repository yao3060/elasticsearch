<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'PUT  v1/logs/<id:\d+>' => 'log/update',
        'POST v1/logs' => 'log/create',
        'GET  v1/logs' => 'log/index',
        'POST v1/asset/search' => 'asset/search',
        'POST v1/asset/recommend-search' => 'asset/recommend-search',
        'POST v1/asset/save-record' => 'asset/save-record',
        'POST v1/background/search' => 'background/search',
        'GET  v1/svgs' => 'es/svg/index',
    ],
];
