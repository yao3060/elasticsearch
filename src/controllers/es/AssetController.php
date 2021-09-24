<?php

/**
 * 重构ES,Asset搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\Asset;
use app\queries\ES\AssetSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class AssetController extends BaseController
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
                $data = (new Asset())
                    ->search(new AssetSearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['pageSize'] ?? 40,
                        $data['sceneId'] ?? 0,
                        $data['isZb'] ?? 0,
                        $data['sort'] ?? 0,
                        $data['useCount'] ?? 0
                    ));
                $response = new Response('get_asset_list', 'assetList', $data);
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

    public function actionRecommendSearch(Request $request)
    {
        $data = $request->get();
        try {
            $model = DynamicModel::validateData($data, [
                ['keyword', 'required']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new Asset())
                    ->recommendSearch(new AssetSearchQuery($data['keyword'], $data['page'], $data['pageSize']));
                $response = new Response('get_Recommend_list', 'Get List', $data);
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

    public function actionSaveRecord()
    {
        $data = Yii::$app->request->post();
        $data = (new Asset())
            ->saveRecord($data);
        $this->response->headers->set('X-Total', 1000);
        return $this->response($data);
    }
}
