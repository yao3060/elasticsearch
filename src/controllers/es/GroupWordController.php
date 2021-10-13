<?php

/**
 * 重构ES,GroupWords搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\GroupWord;
use app\queries\ES\GroupWordSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class GroupWordController extends BaseController
{
    /**
     * @api {get} /v1/groups GetGroupWordSearch
     * @apiName GetGroupWord
     * @apiGroup GroupWord
     * @apiParam (备注)search和keyword为0时，searchAll要等1
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String} [search] 搜索关键词
     * @apiParam (请求参数) {String} [search_all] 多个搜索关键词
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
                $data = (new GroupWord())
                    ->search(new GroupWordSearchQuery(
                        $data['keyword'] ?? 0,
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 40,
                        $data['search'] ?? 0,
                        $data['search_all'] ?? 0
                    ));
                $response = new Response(
                    'get_group_words_list',
                    'GroupWordsList',
                    $data
                );
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
