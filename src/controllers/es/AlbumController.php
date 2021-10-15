<?php

/**
 * 重构ES,Album搜索模块
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\Album;
use app\queries\ES\AlbumSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class AlbumController extends BaseController
{
    /**
     * @api {get} /v1/albums 关键词专题搜索
     * @apiName 关键词专题搜索
     * @apiGroup Album
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {String} [sort_type] 排序  byyesday：昨日热门 ；bymonth：热门下载；bytime：最新上传
     * @apiParam (请求参数) {Boolean} [is_zb] 是否可商用   >= 1 可商用
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String[]} [class_id] 分类
     * @apiParam (请求参数) {Number} [update] 1：强制回源
     * @apiParam (请求参数) {Number} [fuzzy] 1：关键词（有交集）就会出现   但会降低性能  实测 3个词速度降低50%  8个词速度降低87.5%
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {String[]} data.score 计算分数
     * @apiSuccess (应答字段) {Number} data.total 模板数量
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
                $data = (new Album())
                    ->search(new AlbumSearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 5,
                        $data['class_id'] ?? 0,
                        $data['type'] ?? 2,
                        $data['sort_type'] ?? 'default',
                        $data['update'] ?? 0,
                        $data['fuzzy'] ?? 0,
                    ));
                $response = new Response('get_album_list', 'AlbumList', $data);
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
