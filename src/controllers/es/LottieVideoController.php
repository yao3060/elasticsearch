<?php


namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\LottieVideo;
use app\queries\ES\LottieVideoSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class LottieVideoController extends BaseController
{
    /**
     * @api {get} /v1/lottie-videos Get Lottie Video
     * @apiName GetLottieVideo
     * @apiGroup LottieVideo
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [ratio] 版式  null：全部 1：横图；2：竖图；0：方图
     * @apiParam (请求参数) {string} [class_id] 分类
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
            $validate = DynamicModel::validateData(
                $request->getQueryParams(),
                [
                    [['keyword'], 'string']
                ]
            );

            if ($validate->hasErrors()) {
                return $this->response(
                    new Response(
                        'validate params error',
                        'Validate Params Error',
                        $validate->errors,
                        422
                    )
                );
            }

            $validateAttributes = $validate->getAttributes();

            $search = (new LottieVideo())->search(
                new LottieVideoSearchQuery(
                    keyword: $validateAttributes['keyword'] ?? 0,
                    classId: $validateAttributes['class_id'] ?? [],
                    page: $validateAttributes['page'] ?? 1,
                    pageSize: $validateAttributes['page_size'] ?? 40,
                    prep: $validateAttributes['prep'] ?? 0
                )
            );

            $response = new Response('lottie_video_search', 'Lottie Video Search', $search);
        } catch (UnknownPropertyException $unknownException) {
            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [],
                422
            );
        } catch (\Throwable $throwable) {
            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage() . $throwable->getFile() . $throwable->getLine(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500
            );
        }

        return $this->response($response);
    }
}
