<?php

namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\Svg;
use app\queries\ES\SvgSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class SvgController extends BaseController
{
    /**
     * @api {get} /v1/svg GetSvgSearch
     * @apiName GetSvgSearch
     * @apiGroup SvgSearch
     * @apiParam (备注)search和keyword为0时，searchAll要等1
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String} [kid2] 二级版式
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.hit 命中数
     * @apiSuccess (应答字段) {String[]} data.ids 模板id集合
     * @apiSuccess (应答字段) {Object[]} data.score 计算分数
     */
    public function actionIndex(Request $request)
    {

        try {
            $model = DynamicModel::validateData($request->get(), [
                ['keyword', 'string']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $items = (new Svg)->search(new SvgSearchQuery(
                    keyword: $request->get('keyword', 0),
                    kid2: $request->get('kid2', 0),
                    page: $request->get('page', 1),
                    pageSize: $request->get('page_size', 40)
                ));
                $response = new Response('get_svg_list', 'Get Svg List', $items);
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
    }
}
