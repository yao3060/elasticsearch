<?php

/**
 * 重构ES,GroupWords搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\TemplateSinglePage;
use app\queries\ES\TemplateSinglePageSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class TemplateSinglePageController extends BaseController
{
    public function actionSearch(Request $request)
    {
        $data = $request->get();
        try {
            $model = DynamicModel::validateData($data, [
                ['c1', 'required']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new TemplateSinglePage())
                    ->search(new TemplateSinglePageSearchQuery(
                        $data['class_id'],
                        $data['page'] ?? 1,
                        $data['page_size'] ?? 50,
                        $data['class_level2_ids'] ?? [],
                        $data['class_level3_ids'] ?? []
                    ));
                $response = new Response('get_template_single_page_list', 'TemplateSinglePageList', $data);
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
