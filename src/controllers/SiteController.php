<?php

namespace app\controllers;

use app\models\Backend\AssetUseTop;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

class SiteController extends Controller
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
        return $this->asJson([
            'code' => 'welcome',
            'message' => 'Welcome',
            'data' => [
                'is_prod' => is_prod(),
                'is_local' => is_local(),
                'AssetUseTop' => AssetUseTop::getLatestBy('kid_1', 1),
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
        $x = 0.0001;
        for ($i = 0; $i <= 10000000; $i++) {
            $x += sqrt($x);
        }
        return "OK!";
    }
}
