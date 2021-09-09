<?php

/**
 * 重构ES,background搜索方法
 */
namespace app\controllers\es;
use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\Background;
use app\queries\ES\BackGroundSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class BackgroundController extends BaseController
{
    /**
     * @return \yii\web\Response
     */
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
                $data = (new Background())
                    ->search(new BackGroundSearchQuery($data['keyword'], $data['page'] ?? 1, $data['pageSize'] ?? 40,
                        $data['sceneId'] ?? 0,$data['isZb'] ?? 0,$data['sort'] ?? 0,$data['useCount'] ?? 0,$data['kid'] ?? 0,
                        $data['ratioId'] ?? 0,$data['class'] ?? 0 ,$data['isBg'] ?? 0));
                $response = new Response('get_list', 'Get List', $data);
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
