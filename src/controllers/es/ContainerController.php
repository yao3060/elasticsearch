<?php

/**
 * 重构ES,Container搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\Container;
use app\queries\ES\ContainerSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class ContainerController extends BaseController
{
    /**
     * @api {get} /v1/containers GetContainerSearch
     * @apiName GetContainer
     * @apiGroup Container
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [kid] 版式
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
            $data = (new Container())
                ->search(new ContainerSearchQuery(
                    $data['keyword'] ?? 0,
                    $data['page'] ?? 1,
                    $data['page_size'] ?? 40,
                    $data['kid'] ?? 0,
                ));
            $response = new Response(
                'get_container_list',
                'ContainerList',
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

    /*public function actionRecommendSearch(Request $request)
    {
        $data = $request->get();
        try {
            $model = DynamicModel::validateData($data, [
                ['keyword', 'required']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new VideoElement())
                    ->recommendSearch(new VideoElementsSearchQuery(
                        $data['keyword'],
                        $data['page'],
                        $data['pageSize']
                    ));
                $response = new Response('get_Group_Recommend_list', 'Group_Recommend_List', $data);
            }
        } catch (UnknownPropertyException $e) {
            $response = new Response(
                StringHelper::snake($e->getName()),
                str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()),
                [],
                422
            );
        } catch (\Throwable $th) {
            $response = new Response(
                'internal_server_error',
                $th->getMessage(),
                YII_DEBUG ? explode("\n", $th->getTraceAsString()) : [],
                500
            );
        }
        return $this->response($response);
    }*/
}
