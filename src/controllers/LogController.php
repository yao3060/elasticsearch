<?php

namespace app\controllers;

use yii\web\Request;

class LogController extends BaseController
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
        $code = 'readable_response_code';
        $message = 'Response Message';
        try {
            $data = ['response data'];
            $statusCode = 200;
        } catch (\Throwable $th) {
            $statusCode = 500;
            $code = 'a_readable_error_code';
            $message = $th->getMessage();
            $data = YII_DEBUG ? explode("\n", $th->getTraceAsString()) : [];
        }

        return $this->response($code, $message, $data, $statusCode, [
            'X-Total' => 100,
            'X-UserId' => 199
        ]);
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
