<?php

/**
 * 重构ES,GifAsset搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\GifAsset;
use app\queries\ES\GifAssetSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class GifAssetController extends BaseController
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
                $data = (new GifAsset())
                    ->search(new GifAssetSearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['pageSize'] ?? 40,
                        $data['classId'] ?? 0,
                        $data['isZb'] ?? 0,
                        $data['prep'] ?? 0,
                        $data['limitSize'] ?? 0,
                    ));
                $response = new Response('get_groupWords_list', 'groupWordsList', $data);
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
                'a_readable_error_code',
                $th->getMessage(),
                YII_DEBUG ? explode("\n", $th->getTraceAsString()) : [],
                500
            );
        }
        return $this->response($response);
    }

}
