<?php

namespace app\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;
use app\components\Response;
use app\helpers\StringHelper;
use yii\filters\auth\HttpBasicAuth;

class LogController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::class,
            'auth' => [$this, 'auth'],
        ];
        return $behaviors;
    }

    public function auth($username, $password)
    {
        return new \app\models\GenericUser([
            'id' => '101',
            'username' => $username,
            'password' => $password,
            'auth_key' => 'test101key',
            'access_token' => '101-token',
        ]);
    }

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
        $response = new Response();

        try {
            $model = DynamicModel::validateData($request->get(), [
                [['name', 'email'], 'string', 'max' => 128],
                ['email', 'email'],
            ]);


            $response->status(200);
            $response->code('readable_response_code');
            $response->message('Response Message');
            $response->data(array_merge(
                $model->getAttributes(),
                ['user' => Yii::$app->user->identity]
            ));
        } catch (UnknownPropertyException $e) {

            $response->status(500);
            $response->code(StringHelper::snake($e->getName()));
            $response->message(str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()));
            $response->data([]);
            $response->headers([
                'X-Total' => 100,
                'X-UserId' => 199
            ]);
        } catch (\Throwable $th) {

            $response->status(500);
            $response->code('a_readable_error_code');
            $response->message($e->getMessage());
            $response->data(YII_DEBUG ? explode("\n", $th->getTraceAsString()) : []);
        }

        return $this->response($response);
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
