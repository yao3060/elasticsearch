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
    public function actionSearch(Request $request)
    {

        $data = $request->get();
        try {
            $model = DynamicModel::validateData($data, [
                ['keyword', 'required']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new Picture())
                    ->search(new PictureSearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 40,
                        $data['scene_id'] ?? 0,
                        $data['is_zb'] ?? 1,
                        $data['kid'] ?? 0,
                        $data['vip_pic'] ?? 0,
                        $data['ratio_id'] ?? 0
                    ));
                $response = new Response('get_picture_list', 'PictureList', $data);
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
