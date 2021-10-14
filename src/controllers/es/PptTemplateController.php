<?php

/**
 * 重构ES,GroupWords搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\PptTemplate;
use app\queries\ES\PptTemplateSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class PptTemplateController extends BaseController
{
    /**
     * @api {get} /v1/ppt-templates GetPptTemplateSearch
     * @apiName GetPptTemplateSearch
     * @apiGroup PptTemplate
     *
     * @apiParam (请求参数) {String} class_id 一级类目
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String[]} [class_level2_ids] 二级类目
     * @apiParam (请求参数) {String[]} [class_level3_ids] 三级类目
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {Object[]} data.score 计算分数
     * @apiSuccess (应答字段) {Number} data.total 模板数量
     */
    public function actionSearch(Request $request)
    {
        $data = $request->get();
        try {
            $data = (new PptTemplate())
                ->search(new PptTemplateSearchQuery(
                    $data['class_id'] ?? 0,
                    $data['page'] ?? 1,
                    $data['page_size'] ?? 50,
                    $data['class_level2_ids'] ?? [],
                    $data['class_level3_ids'] ?? []
                ));
            $response = new Response('get_template_single_page_list', 'TemplateSinglePageList', $data);
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
