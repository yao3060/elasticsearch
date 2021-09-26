<?php

namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\Svg;
use app\queries\ES\SvgSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class SvgController extends BaseController
{

    public function actionIndex(Request $request)
    {

        try {
            $model = DynamicModel::validateData($request->get(), [
                ['keyword', 'string']
            ]);
            if ($model->hasErrors()) {
                $response = new Response('unprocessable_entity', 'Unprocessable Entity', $model->errors, 422);
            } else {
                $items = (new Svg)->search(new SvgSearchQuery(
                    keyword: $request->get('keyword', 0),
                    kid2: $request->get('kid2', []),
                    page: $request->get('page', 1),
                    pageSize: $request->get('page_size', 40)
                ));
                $response = new Response('get_svg_list', 'Get Svg List', $items);
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
}
