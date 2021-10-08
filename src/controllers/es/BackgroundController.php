<?php

/**
 * 重构ES,background搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\Background;
use app\queries\ES\BackgroundSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class BackgroundController extends BaseController
{
    /**
     * @api {get} /v1/backgrounds Get BackgroundSearch
     * @apiName GetBackground
     * @apiGroup Background
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String[]} [scene_id]  场景id
     * @apiParam (请求参数) {Number} [is_zb] 是否可商用   >= 1 可商用
     * @apiParam (请求参数) {string} [sort]  排序
     * @apiParam (请求参数) {string} [use_count]  使用计数
     * @apiParam (请求参数) {String[]} [kid] 版式
     * @apiParam (请求参数) {Number}  [ratio_id] 版式  null：全部 1：横图；2：竖图；0：方图
     * @apiParam (请求参数) {String[]} [class]  分类
     * @apiParam (请求参数) {Number} [is_bg] 是否是背景
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
        $data = $request->get();
        try {
            $model = DynamicModel::validateData($data, [
                ['keyword', 'string']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new Background())
                    ->search(new BackgroundSearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 40,
                        $data['scene_id'] ?? 0,
                        $data['is_zb'] ?? 0,
                        $data['sort'] ?? 0,
                        $data['use_count'] ?? 0,
                        $data['kid'] ?? 0,
                        $data['ratio_id'] ?? 0,
                        $data['class'] ?? 0,
                        $data['is_bg'] ?? 0
                    ));
                $response = new Response('get_background_list', 'BackGroundList', $data);
            }
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
