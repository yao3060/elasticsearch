<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\BackgroundVideo;
use app\queries\ES\BackgroundVideoQuery;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class BackgroundVideoController extends BaseController
{
    /**
     * @api {get} /v1/background-videos Get Background Video
     * @apiName GetBackgroundVideo
     * @apiGroup BackgroundVideo
     * @apiDescription 背景视频搜索（原 ips_backend 项目模型：ESBgVideo）
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [ratio] 版式  null：全部 1：横图；2：竖图；0：方图
     * @apiParam (请求参数) {string} [class_id] 分类
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

            $search = (new BackgroundVideo())->search(
                new BackgroundVideoQuery(
                    keyword: $queries['keyword'] ?? 0,
                    classId: $queries['class_id'] ?? [],
                    page: $queries['page'] ?? 1,
                    pageSize: $queries['page_size'] ?? 40,
                    ratio: $queries['ratio'] ?? 0
                )
            );

            $response = new Response('background_video_search', 'Background Video Search', $search);
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
                $throwable->getMessage(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500
            );
        }

        return $this->response($response);
    }
}
