<?php

namespace app\controllers;

use app\models\Backend\AssetUseTop;
use Yii;
use yii\filters\AccessControl;
use app\components\Response;
use app\helpers\StringHelper;
use yii\filters\VerbFilter;

class SiteController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionError()
    {
        /** @var \yii\web\HttpException $exception */
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            return $this->response(new Response(
                StringHelper::snake($exception->getName()),
                $exception->getMessage(),
                YII_DEBUG ? $exception->getTrace() : [],
                $exception->statusCode
            ));
        }
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        try {
            Yii::info('this is info.', __METHOD__);
        } catch (\Throwable $th) {
            Yii::error($th);
            Yii::error($th->getTraceAsString());
        }

        return $this->asJson([
            'code' => 'welcome',
            'message' => 'Welcome',
            'data' => [
                'is_prod' => is_prod(),
                'is_local' => is_local(),
                'AssetUseTop' => AssetUseTop::getLatestBy('kid_1', 1),
                'profile' => Yii::$app->user->identity,
            ]
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        return 'this is a login action';
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        return 'this is a logout action';
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        return $this->asJson(['message' => 'this is a contact page']);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->asJson(['message' => 'this is a about action']);
    }

    public function actionHpa()
    {
        if (is_prod()) {
            return 'IS PROD. Exit.';
        }

        $x = 0;
        $times = 9000000;
        for ($i = 0; $i <= $times; $i++) {
            $x += sqrt($times);
        }
        return "Sum of $times time sqrt($times):$x" . PHP_EOL;
    }
}
