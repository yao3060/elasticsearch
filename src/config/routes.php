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
        'GET  v1/designer-templates' => 'es/designer-template/index',
        'POST v1/seo/search' => 'es/seo/search',
        'POST v1/seo/seo-search' => 'es/seo/seo-search',
        'GET  v1/templates' => 'es/template/search', // 模板搜索
        'GET  v1/templates/recommends' => 'es/template/recommend-search', // 推荐模板搜索
        'POST  v1/sensitive/word/validate' => 'es/sensitive-word/validate', // 违禁词验证
    ],
];
