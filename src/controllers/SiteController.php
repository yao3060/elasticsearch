<?php

namespace app\controllers;

use app\services\ali\AliDataVisualization;
use app\services\validate\ParamsValidateService;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use app\components\Response;
use app\helpers\StringHelper;
use yii\filters\VerbFilter;
use yii\web\Request;

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
            return $this->response(
                new Response(
                    StringHelper::snake($exception->getName()),
                    $exception->getMessage(),
                    YII_DEBUG ? $exception->getTrace() : [],
                    $exception->statusCode
                )
            );
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

        return $this->asJson(
            [
                'code' => 'welcome',
                'message' => 'Welcome',
                'data' => [
                    'is_prod' => is_prod(),
                    'is_local' => is_local(),
                    'env' => getenv('APP_ENV') ?? 'dev',
                    'version' => getenv('APP_VERSION') ?: '0.0.0',
                    'core_version' => Yii::getVersion(),
                    'profile' => Yii::$app->user->identity ?? '',
                ]
            ]
        );
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
        return "Sum of $times time sqrt($times):$x".PHP_EOL;
    }

    public function actionDashboard(Request $request)
    {
        try {
            $queries = $request->getQueryParams();
            $paramValidate = new ParamsValidateService();
            $validate = $paramValidate->validate($queries, [
                [['project_name', 'log_store_name'], 'required'],
                ['project_name', 'string'],
                ['log_store_name', 'string']
            ]);
            if ($validate == false) {
                return $this->asJson(
                    [
                        'code' => 'validate_params_error',
                        'message' => $paramValidate->getFirstErrorSummary()
                    ]
                );
            }
            $validateAttributes = $paramValidate->getAttributes();
            $projectName = $validateAttributes['project_name'];
            $logStoreName = $validateAttributes['log_store_name'];
            $except = ['project_name', 'log_store_name'];
            $otherParams = array_filter(
                $request->getQueryParams(),
                function ($key) use ($except) {
                    return !in_array($key, $except);
                },
                ARRAY_FILTER_USE_KEY
            );
            $responseUrl = (new AliDataVisualization(
                projectName: $projectName,
                logStoreName: $logStoreName,
                otherParams: $otherParams
            ))->getSignInUrl();
            if ($responseUrl['code'] == 'get_sign_url' && isset($responseUrl['url']) && $responseUrl['url']) {
                Header("Location: ".$responseUrl['url']);
                exit;
            }
            return $this->asJson(
                [
                    'code' => $responseUrl['code'],
                    'message' => $responseUrl['message']
                ]
            );
        } catch (\Throwable $e) {
            return $this->asJson(
                [
                    'code' => 'error',
                    'message' => "{$e->getMessage()}, File: {$e->getFile()}, Line: {$e->getLine()}"
                ]
            );
        }
    }
}
