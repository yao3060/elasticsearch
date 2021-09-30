<?php

/**
 * 重构ES,SeoSearchWordAsset搜索方法
 * ysp
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\SeoSearchWordAsset;
use app\queries\ES\SeoSearchWordAssetQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class SeoSearchWordAssetController extends BaseController
{
    /**
     * @api {get} /v1/seo/keyword-assets Seo Search Word Asset
     * @apiName GetSeoWordAsset
     * @apiGroup SeoWordAsset
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {Number} [type]
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String[]} data.list
     * @apiSuccess (应答字段) {Number} data.total 数据总量
     *
     * @apiSuccessExample {json} 应答事例
     *  {
     *     "code": "get_seo_search_word_asset_list",
     *     "message": "Seo Search Word Asset List",
     *     "data": {
     *          "total": 273,
     *          "list": [
     *              {
     *                    "id": 407238,
     *                    "keyword": "",
     *                    "pinyin": "234fdd84877d2f7501878b44603915d0",
     *                    "weight": 3
     *              },
     *              {
     *                    "id": 441117,
     *                    "keyword": "",
     *                    "pinyin": "logolvyou",
     *                    "weight": 0
     *              }
     *
     *              ....
     *
     *         ]
     *      }
     *  }
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
                $data = (new SeoSearchWordAsset())
                    ->seoSearch(new SeoSearchWordAssetQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 40,
                        $data['type'] ?? 1,
                    ));
                $response = new Response('get_seo_search_word_asset_list', 'Seo Search Word Asset List', $data);
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
