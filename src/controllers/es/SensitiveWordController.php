<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\SensitiveWord;
use app\queries\ES\SensitiveWordSearchQuery;
use yii\base\DynamicModel;
use yii\web\Request;
use yii\web\UnauthorizedHttpException;

class SensitiveWordController extends BaseController
{
    /**
     * 违禁词验证
     * @param Request $request
     * @return \yii\web\Response
     */
    public function actionValidate(Request $request)
    {
        try {

            $validate = DynamicModel::validateData($request->post(), SensitiveWord::validateRules());

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate_params_error',
                    'Validate Params Error',
                    $validate->errors, 422
                ));
            }

            $search = (new SensitiveWord())->search(new SensitiveWordSearchQuery($validate->getAttributes()));

            $response = new Response('sensitive word validate', 'Sensitive Word Validate', $search);

        } catch (UnauthorizedHttpException $unknownException) {

            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [], 422);

        } catch (\Throwable $throwable) {

            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500);

        }

        return $this->response($response);
    }
}
