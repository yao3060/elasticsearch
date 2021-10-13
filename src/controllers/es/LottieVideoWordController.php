<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\models\ES\LottieVideoWord;
use app\queries\ES\LottieVideoWordSearchQuery;
use yii\web\Request;

class LottieVideoWordController extends BaseController
{
    /**
     * @api {get} /v1/lottie-video-words Get Lottie Video Word
     * @apiName GetLottieVideoWord
     * @apiGroup LottieVideoWord
     * @apiDescription 设计师动效搜索词搜索（原 ips_backend 项目模型：ESVideoLottieWord）
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

            $queries = $request->getQueryParams();

            $search = (new LottieVideoWord())->search(new LottieVideoWordSearchQuery(
                keyword: $queries['keyword'] ?? 0,
                page: $queries['page'] ?? 1,
                pageSize: $queries['page_size'] ?? 40,
                prep: $queries['prep'] ?? 0
            ));

            $response = new Response('lottie_video_word_search', 'Lottie Video Word Search', $search);

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
