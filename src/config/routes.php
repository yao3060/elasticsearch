<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'PUT  v1/logs/<id:\d+>' => 'log/update',
        'POST v1/logs' => 'log/create',
        'GET  v1/logs' => 'log/index',
        'GET  v1/svg' => 'es/svg/index',

        //Y
        'GET  v1/seo/recommends' => 'es/seo/seo-search', // SEO推荐关键词
        'GET  v1/seo/keyword-assets' => 'es/seo-search-word-asset/search', //seo词库相关推荐
        'GET  v1/seo/title-keywords' => 'es/seo-detail-keyword-for-title/search', //SEO标题中的关键词
        'GET  v1/seo/link-word' => 'es/seo-link-word/search', //seo词库中 相关搜索词
        'GET  v1/seo/link-word-seo' => 'es/seo-link-word/seo-search', //seo词库中 相关搜索词
        'GET  v1/seo/keywords' => 'es/seo/search', //seo词库中 相关搜索词
        'GET  v1/keywords' => 'es/search-word/search', //关键词搜索



        'GET  v1/assets/recommends' => 'es/asset/recommend-search',
        'GET  v1/assets' => 'es/asset/search', //素材搜索

        'GET  v1/backgrounds' => 'es/background/search', //背景搜索
        'GET  v1/gif-assets' => 'es/gif-asset/search', //搜索
        'GET  v1/pictures' => 'es/picture/search', //图片素材搜索

        'GET  v1/groups/recommends' => 'es/group-words/recommend-search',
        'GET  v1/groups' => 'es/group-words/search', //组合字搜索

        'GET  v1/audiovisuals' => 'es/video-audio/search', //试听素材搜索
        'GET  v1/video-elements' => 'es/video-e/search', //视频元素搜索

        'GET  v1/containers' => 'es/container/search', //裁剪搜索
        'POST  v1/h5-sensitive-words' => 'es/h5-ban-words/h5-ban-search', //查询是否存在敏感词

        'GET  v1/ppt-templates' => 'es/template-single-page/search', //PPT模板单页分类筛选


        //H
        'GET  v1/designer-templates' => 'es/designer-template/index', // 设计师模板
        'GET  v1/templates/recommends' => 'es/template/recommend-search', // 推荐模板搜索
        'GET  v1/templates' => 'es/template/search', // 模板搜索
        'POST v1/sensitive-words/validate' => 'es/sensitive-word/validate', // 违禁词验证
        'GET  v1/background-videos' => 'es/background-video/search', // 背景视频搜索
        'GET  v1/rich-editor-assets' => 'es/rich-editor-asset/search', // 富文本元素
        'GET  v1/video-templates' => 'es/video-template/search', // 片段视频
        'GET  v1/lottie-videos' => 'es/lottie-video/search', // 设计师动效
        'GET  v1/lottie-video-words' => 'es/lottie-video-word/search', // 设计师动效搜索词
        'GET  v1/seo-new-pages' => 'es/seo-new-page/seo-search', // seo
    ],
];
