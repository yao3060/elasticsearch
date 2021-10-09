<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\LottieVideoWord;
use app\queries\ES\LottieVideoWordSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class LottieVideoWordController extends BaseController
{
    /**
     * @api {get} /v1/lottie-video-words Get Lottie Video Word
     * @apiName GetLottieVideoWord
     * @apiGroup LottieVideoWord
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [prep] 强制回源
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {String[]} data.score 计算分数
     */
    public function actionSearch(Request $request)
    {
        try {

            $validate = DynamicModel::validateData($request->getQueryParams(), []);

            if ($validate->hasErrors()) {
                if ($validate->hasErrors()) {
                    return $this->response(new Response(
                        'validate params error',
                        'Validate Params Error',
                        $validate->errors,
                        422));
                }
            }

            $validateAttributes = $validate->getAttributes();

            $search = (new LottieVideoWord())->search(new LottieVideoWordSearchQuery(
                keyword: $validateAttributes['keyword'] ?? 0,
                page: $validateAttributes['page'] ?? 1,
                pageSize: $validateAttributes['page_size'] ?? 40,
                prep: $validateAttributes['prep'] ?? 0
            ));

            $response = new Response('lottie_video_word_search', 'Lottie Video Word Search', $search);

        } catch (UnknownPropertyException $unknownException) {

            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [],
                422);

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
