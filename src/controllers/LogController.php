<?php

namespace app\controllers;

use yii\rest\Controller;
use yii\web\Request;

class LogController extends Controller
{
    /**
     * @api {get} /v1/logs Request Logs
     * @apiName GetLogs
     * @apiGroup Logs
     *
     * @apiParam {Number} [page=1]          Current Page
     * @apiParam {Number} [per_page=10]     Page Size
     */
    public function actionIndex(Request $request)
    {
        $data = [
            'params' => $request->get(),
            'user_id' =>  $request->headers->get('X-UserId'),
            'roles' =>  $request->headers->get('X-Roles'),
            'token'  => $request->headers->get('Authorization'),
            'total' => 1000
        ];
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }

    public function actionCreate(Request $request)
    {
        $this->response->headers->set('Pragma', 'no-cache');
        return $this->asJson($request->post());
    }

    public function actionUpdate(Request $request, int $id)
    {
        $data = [
            'id' => $id,
            'params' => $request->getQueryParams(),
            'body' => $request->post(),
        ];

        // the way to send HTTP Headers
        // @see https://www.yiiframework.com/doc/guide/2.0/en/runtime-responses#http-headers
        $this->response->headers->set('Pragma', 'no-cache');

        return $this->asJson($data);
    }
}
