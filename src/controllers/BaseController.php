<?php

namespace app\controllers;

use app\components\AppLogTarget;
use Yii;
use app\components\IpsAuthority;
use app\interfaces\ResponseInterface;
use yii\rest\Controller;
use app\models\User;

class BaseController extends Controller
{
    public function init()
    {
        parent::init();
        IpsAuthority::definedAuth(); // 初始化权限变量

//        Yii::$app->on(yii\web\Application::EVENT_BEFORE_REQUEST, function ($event) {
//            \Yii::info(str_repeat("=", 100));
//        });
//
//        Yii::$app->on(yii\web\Application::EVENT_AFTER_REQUEST, function ($event) {
//            \Yii::info(str_repeat("=", 100));
//        });
//        Yii::$app->on(
//            yii\web\Application::EVENT_AFTER_REQUEST,
//            function ($event) {
//                $moduleId = Yii::$app->controller->module->id;
//                if (in_array($moduleId, array('ips-elasticsearch'))) {
//                    $requestParams = Yii::$app->request->getBodyParams();
//                    $route = Yii::$app->controller->getRoute();
//                    $requestUrl = Yii::$app->request->getHostInfo().Yii::$app->request->getUrl();
//
//                    $data = ob_get_contents();
//
//
////                    \Yii::beginProfile(str_repeat("=", 100));
//                    \Yii::info($requestUrl, $route);
//                    \Yii::info('Params：'.json_encode($requestParams));
//                    \Yii::info('Return：'.$data);
////                    \Yii::endProfile(str_repeat("=", 100));
//
//                }
//            }
//        );
    }

    public function beforeAction($action)
    {
        // add login user
        $headers = Yii::$app->request->headers;
        if ($headers->get('X-UserId', 0)) {
            $user = [
                'id' => $headers->get('X-UserId', 0),
                'username' => $headers->get('X-Username', ''),
                'password' => '',
                'authKey' => 'fake_user_auth_key',
                'accessToken' => 'fake_user_access_token',
                'type' => $headers->get('X-UserType'),
                'roles' => explode(',', $headers->get('X-Roles'))
            ];
            User::append($user['id'], $user);
            Yii::$app->user->login(User::findIdentity($user['id']));
        }

        return $action;
    }


    /**
     * common response function
     *
     * @param  ResponseInterface  $response
     * @return \yii\web\Response
     */
    public function response(ResponseInterface $response)
    {
        $this->response->setStatusCode($response->get('status'));

        if (count($response->headers)) {
            array_walk(
                $response->headers,
                function ($value, $key) {
                    $this->response->headers->set($key, $value);
                }
            );
        }

        return $this->asJson(
            [
                'code' => $response->get('code'),
                'message' => $response->get('message'),
                'data' => $response->get('data')
            ]
        );
    }
}
