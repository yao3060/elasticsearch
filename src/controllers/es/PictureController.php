<?php

/**
 * 重构ES,Asset搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\Picture;
use app\queries\ES\PictureSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class PictureController extends BaseController
{
    /**
     * @api {get} /v1/pictures 图片素材搜索
     * @apiName 图片素材搜索
     * @apiGroup PictureSearch
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String} [scene_id] 场景id
     * @apiParam (请求参数) {String} [is_zb] 是否可商用   >= 1 可商用
     * @apiParam (请求参数) {String[]} [kid] 版式
     * @apiParam (请求参数) {Number} [vip_pic] vip类型：0 所有元素， 1 非vip元素， 2 vip元素
     * @apiParam (请求参数) {Number} [ratio_id] 版式  null：全部 1：横图；2：竖图；0：方图
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {Object[]} data.score 计算分数
     */
    public function actionSearch(Request $request)
    {

        $data = $request->get();
        try {
            $data = (new Picture())
                ->search(new PictureSearchQuery(
                    $data['keyword'] ?? 0,
                    $data['page'] ?? 1,
                    $data['page_size'] ?? 40,
                    $data['scene_id'] ?? 0,
                    $data['is_zb'] ?? 1,
                    $data['kid'] ?? 0,
                    $data['vip_pic'] ?? 0,
                    $data['ratio_id'] ?? 0
                ));
            $response = new Response('get_picture_list', 'PictureList', $data);
        } catch (UnknownPropertyException $e) {
            $response = new Response(
                StringHelper::snake($e->getName()),
                str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()),
                [],
                422
            );
            yii::error(str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()), __METHOD__);
        } catch (\Throwable $th) {
            $response = new Response(
                'internal_server_error',
                $th->getMessage(),
                YII_DEBUG ? explode("\n", $th->getTraceAsString()) : [],
                500
            );
            yii::error($th->getMessage(), __METHOD__);
        }
        return $this->response($response);
    }
}
