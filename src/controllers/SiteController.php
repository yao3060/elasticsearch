<?php

namespace app\controllers;

use app\models\Backend\AssetUseTop;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;
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

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
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
            Yii::warning('this is warning.', __METHOD__);
            Yii::error([
                'this' => 'This',
                'is' => 'is',
                'error' => 'error.'
            ], __METHOD__);

            throw new \Exception('this is a exception.');
        } catch (\Throwable $th) {

            Yii::error($th);
            Yii::error($th->getTraceAsString());
            //throw $th;
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
        return 'this is a contact page';
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return 'this is a about action';
    }

    public function actionHpa()
    {
        if (is_prod()) {
            return 'IS PROD. Exit.';
        }

        $x = 0.0001;
        for ($i = 0; $i <= 50000000; $i++) {
            $x += sqrt($x);
        }
        return "OK!";
    }
}
