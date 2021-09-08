<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'PUT  v1/logs/<id:\d+>' => 'log/update',
        'POST v1/logs' => 'log/create',
        'GET  v1/logs' => 'log/index',
        'GET  v1/svgs' => 'es/svg/index',
        'GET  v1/assets' => 'es/asset/search',
        'GET  v1/assets/recommends' => 'es/asset/recommend-search',
        'GET  v1/backgrounds' => 'es/background/search',
        'GET  v1/seos' => 'es/seo/search',
        'GET  v1/seos/seos' => 'es/seo/seo-search',
        'GET  v1/pictures' => 'es/picture/search',
        'GET  v1/templates' => 'es/template/search',
        'POST v1/templates' => 'es/template/store',
        'GET  v1/templates/recommends' => 'es/template/recommend-search',
        'GET  v1/designer-templates' => 'es/designer-template/index',
    ],
];
