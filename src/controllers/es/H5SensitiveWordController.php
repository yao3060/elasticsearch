<?php

/**
 * 重构ES,H5BanWords搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\H5SensitiveWord;
use app\queries\ES\H5SensitiveWordSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class H5SensitiveWordController extends BaseController
{
    /**
     * @api {post} /v1/h5-sensitive-words/validate 查询是否存在敏感词
     * @apiName 查询是否存在敏感词
     * @apiGroup H5SensitiveWord
     * @apiParam (请求参数) {String} keyword 搜索关键词
     *
     * @apiSuccess (应答字段) {String} code 返回状态码
     * @apiSuccess (应答字段) {String} message 返回消息
     * @apiSuccess (应答字段) {Object[]} data 返回数据
     * @apiSuccess (应答字段) {String} data.flag 返回状态true或false
     * @apiSuccess (应答字段) {String[]} data.word 返回存在的敏感词
     */
    public function actionValidate(Request $request)
    {
        $data = $request->post();
        \Yii::info("Post Params: " . json_encode($data, JSON_UNESCAPED_UNICODE), __METHOD__);
        try {
            $model = DynamicModel::validateData($data, [
                ['keyword', 'required']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new H5SensitiveWord())
                    ->checkBanWord(new H5SensitiveWordSearchQuery(
                        $data['keyword'],
                    ));
                $response = new Response('get_h5_ban_list', 'H5BanList', $data);
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
