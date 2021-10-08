<?php

/**
 * 重构ES,VideoAudio搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\VideoAudio;
use app\queries\ES\VideoAudioSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class VideoAudioController extends BaseController
{
    /**
     * @api {get} /v1/audiovisuals GetVideoAudioSearch
     * @apiName GetVideoAudioSearch
     * @apiGroup VideoAudio
     *
     * @apiParam (请求参数) {String} keyword 搜索关键词
     * @apiParam (请求参数) {Number} [page] 页码
     * @apiParam (请求参数) {Number} [page_size] 每页条数
     * @apiParam (请求参数) {String} [parents_id] 父类id
     * @apiParam (请求参数) {String} [class_id] 分类id
     * @apiParam (请求参数) {Number} [prep] 强制回源
     * @apiParam (请求参数) {String} [is_designer] 是否是设计师
     * @apiParam (请求参数) {String} [is_vip] 是否是vip
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
            $model = DynamicModel::validateData($data, [
                ['keyword', 'string']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new VideoAudio())
                    ->search(new VideoAudioSearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 40,
                        $data['parents_id'] ?? 0,
                        $data['class_id'] ?? 0,
                        $data['prep'] ?? 0,
                        $data['is_designer'] ?? 0,
                        $data['is_vip'] ?? 0
                    ));
                $response = new Response('get_videoAudio_list', 'VideoAudioList', $data);
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
                $data = (new VideoAudio())
                    ->recommendSearch(new VideoAudioSearchQuery(
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
