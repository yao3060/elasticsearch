<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'PUT  v1/logs/<id:\d+>' => 'log/update',
        'POST v1/logs' => 'log/create',
        'GET  v1/logs' => 'log/index',
        'POST v1/asset/search' => 'es/asset/search',
        'POST v1/asset/recommend-search' => 'es/asset/recommend-search',
        'POST v1/asset/save-record' => 'es/asset/save-record',
        'POST v1/background/search' => 'es/background/search',
        'GET  v1/svgs' => 'es/svg/index',
        'POST v1/seo/search' => 'es/seo/search',
        'POST v1/seo/seo-search' => 'es/seo/seo-search',
    ],
];
