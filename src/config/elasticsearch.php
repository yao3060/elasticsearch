<?php

return [
    'elasticsearch' => [
        'class' => 'yii\elasticsearch\Connection',
        'nodes' => [
            ['http_address' => getenv('ELASTIC_HOST')],
        ],
        // set autodetectCluster to false if you don't want to auto detect nodes
        // 'autodetectCluster' => false,
        'dslVersion' => 5, // default is 5
    ],
    'elasticsearch_color' => [
        'class' => 'yii\elasticsearch\Connection',
        'nodes' => [
            ['http_address' => getenv('ELASTIC_COLOR_HOST')],
        ],
    ],
    'elasticsearch_second' => [
        'class' => 'yii\elasticsearch\Connection',
        'nodes' => [
            ['http_address' => getenv('ELASTIC_SECOND_HOST')],
        ],
    ],
    'elasticsearch_search_keyword' => [
        'class' => 'yii\elasticsearch\Connection',
        'nodes' => [
            ['http_address' => getenv('ELASTIC_KEYWORD_HOST')],
        ],
    ],
];
