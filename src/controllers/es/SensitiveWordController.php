<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\SensitiveWord;
use app\queries\ES\SensitiveWordSearchQuery;
use app\services\validate\ParamsValidateService;
use yii\web\Request;
use yii\web\UnauthorizedHttpException;

class SensitiveWordController extends BaseController
{
    /**
     * @api {post} /v1/sensitive-words/validate Validate Sensitive Word
     * @apiName SensitiveWordValidate
     * @apiGroup SensitiveWord
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {Boolean=true, false} data.flag 是否违禁词 true:是 false:不是
     */
    public function actionValidate(Request $request)
    {
        try {

            $params = $request->post();

            $paramsValidate = new ParamsValidateService();

            $validate = $paramsValidate->validate($params, SensitiveWord::validateRules());

            if ($validate === false) {
                return $this->response(new Response(
                    'validate_params_error',
                    'Validate Params Error',
                    $paramsValidate->getErrorSummary(true), 422
                ));
            }

            $validateAttributes = $paramsValidate->getAttributes();

            $search = (new SensitiveWord())->search(new SensitiveWordSearchQuery(
                keyword: $validateAttributes['keyword'] ?? '',
            ));

            $response = new Response('sensitive_word_validate', 'Sensitive Word Validate', $search);

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
