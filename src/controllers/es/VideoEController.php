<?php

/**
 * 重构ES,VideoE搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\VideoE;
use app\queries\ES\VideoESearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class VideoEController extends BaseController
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
                $data = (new VideoE())
                    ->search(new VideoESearchQuery(
                        $data['keyword'],
                        $data['page'] ?? 1,
                        $data['pageSize'] ?? 40,
                        $data['classId'] ?? 1,
                        $data['ratio'] ?? 0,
                        $data['scopeType'] ?? 0,
                        $data['owner'] ?? 0,
                    ));
                $response = new Response('get_VideoE_list', 'VideoEList', $data);
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
                $data = (new VideoE())
                    ->recommendSearch(new VideoESearchQuery(
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
                'a_readable_error_code',
                $th->getMessage(),
                YII_DEBUG ? explode("\n", $th->getTraceAsString()) : [],
                500
            );
        }
        return $this->response($response);
    }

}
