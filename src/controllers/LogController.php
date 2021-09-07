<?php

namespace app\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;
use app\components\Response;
use app\helpers\StringHelper;

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



        try {
            $model = DynamicModel::validateData($request->get(), [
                [['name', 'email'], 'string'],
                ['email', 'email'],
            ]);
            if ($model->hasErrors()) {
                return  $this->response(new Response('422', '422', $model->errors));
            }

            // code here...
            return $this->response(new Response(
                'readable_response_code',
                'Response Message',
                array_merge($model->getAttributes(), [
                    'user' => Yii::$app->user->identity
                ]),
                200
            ));
        } catch (UnknownPropertyException $e) {
            return  $this->response(new Response(
                StringHelper::snake($e->getName()),
                str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()),
                [],
                422,
                [
                    'X-Total' => 100,
                    'X-UserId' => 199
                ]
            ));
        } catch (\Throwable $th) {
            $response = new Response();
            $response->status(500);
            $response->code('a_readable_error_code');
            $response->message($e->getMessage());
            $response->data(YII_DEBUG ? explode("\n", $th->getTraceAsString()) : []);
            return $this->response($response);
        }
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
