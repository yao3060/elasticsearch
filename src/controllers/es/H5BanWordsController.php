<?php

/**
 * 重构ES,H5BanWords搜索方法
 */

namespace app\controllers\es;

use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\H5BanWords;
use app\queries\ES\H5BanWordsSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class H5BanWordsController extends BaseController
{
    public function actionH5BanSearch(Request $request)
    {
        $data = $request->post();
        try {
            $model = DynamicModel::validateData($data, [
                ['word', 'required']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $data = (new H5BanWords())
                    ->checkBanWord(new H5BanWordsSearchQuery(
                        $data['word'],
                    ));
                $response = new Response('get_H5BanSearch_list', 'H5BanSearchList', $data);
            }
        } catch (UnknownPropertyException $e) {
            $response = new Response(
                StringHelper::snake($e->getName()),
                str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()),
                [],
                422
            );
            yii::error(str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()),__METHOD__);
        } catch (\Throwable $th) {
            $response = new Response(
                'a_readable_error_code',
                $th->getMessage(),
                YII_DEBUG ? explode("\n", $th->getTraceAsString()) : [],
                500
            );
            yii::error($th->getMessage(),__METHOD__);
        }
        return $this->response($response);
    }


}
