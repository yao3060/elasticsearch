<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'PUT  v1/logs/<id:\d+>' => 'log/update',
        'POST v1/logs' => 'log/create',
        'GET  v1/logs' => 'log/index',
        'GET  v1/svgs' => 'es/svg/index',
        //Y
        'GET  v1/assets' => 'es/asset/search',
        'GET  v1/assets/recommends' => 'es/asset/recommend-search',
        'GET  v1/backgrounds' => 'es/background/search',
        'GET  v1/seos' => 'es/seo/search',
        'GET  v1/seos/seos' => 'es/seo/seo-search',
        'GET  v1/pictures' => 'es/picture/search',
        'GET  v1/groups' => 'es/group-words/search',
        'GET  v1/groups/recommends' => 'es/group-words/recommend-search',

        //H
        'GET  v1/designer-templates' => 'es/designer-template/index',
        'GET  v1/templates' => 'es/template/search', // 模板搜索
        'GET  v1/templates/recommends' => 'es/template/recommend-search', // 推荐模板搜索
        'POST v1/sensitive/word/validate' => 'es/sensitive-word/validate', // 违禁词验证
        'GET  v1/background/videos' => 'es/background-video/search', // 背景视频搜索
    ],
];
