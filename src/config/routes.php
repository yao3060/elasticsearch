<?php

return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'PUT  v1/logs/<id:\d+>' => 'log/update',
        'POST v1/logs' => 'log/create',
        'GET  v1/logs' => 'log/index',
        'GET  v1/svgs' => 'es/svg/index',
        'GET  v1/designer-templates' => 'es/designer-template/index',
    ],
];
