<?php

/**
 * 重构ES,Asset搜索方法
 */
namespace app\controllers\es;
use app\components\Response;
use app\helpers\StringHelper;
use app\models\ES\GroupWords;
use app\queries\ES\GroupWordsSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use app\controllers\BaseController;
use Yii;
use yii\web\Request;

class GroupWordsController extends BaseController
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
                $data = (new GroupWords())
                    ->search(new GroupWordsSearchQuery($data['keyword'], $data['page'], $data['pageSize'],$data['search'],
                        $data['searchAll']));
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
