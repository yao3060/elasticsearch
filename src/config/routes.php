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
        'GET  v1/assets' => 'es/asset/search', //素材搜索
        'GET  v1/assets/recommends' => 'es/asset/recommend-search',
        'GET  v1/backgrounds' => 'es/background/search', //背景搜索
        'GET  v1/seos' => 'es/seo/search', //seo词库中 相关搜索词
        'GET  v1/seos/seos' => 'es/seo/seo-search',
        'GET  v1/pictures' => 'es/picture/search',
        'GET  v1/groups' => 'es/group-words/search',
        'GET  v1/groups/recommends' => 'es/group-words/recommend-search',
        'GET  v1/videos' => 'es/video-audio/search',
        'GET  v1/searchs/word' => 'es/search-word/search',
        'GET  v1/videos/e' => 'es/video-e/search',//搜索

        //H
        'GET  v1/designer-templates' => 'es/designer-template/index', // 设计师模板
        'GET  v1/templates' => 'es/template/search', // 模板搜索
        'GET  v1/templates/recommends' => 'es/template/recommend-search', // 推荐模板搜索
        'POST v1/sensitive/word/validate' => 'es/sensitive-word/validate', // 违禁词验证
        'GET  v1/background/videos' => 'es/background-video/search', // 背景视频搜索
        'GET  v1/rich-editor-assets' => 'es/rich-editor-asset/search', // 富文本元素
        'GET  v1/video-templates' => 'es/video-template/search', // 
    ],
];
