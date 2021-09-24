<?php

namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\DesignerTemplate;
use app\queries\ES\DesignerTemplateSearchQuery;
use yii\web\Request;
use yii\base\UnknownPropertyException;

class DesignerTemplateController extends BaseController
{

    public function actionIndex(Request $request)
    {
        try {
            $items = (new DesignerTemplate)->search(
                new DesignerTemplateSearchQuery(
                    keyword: $request->get('keyword', 0),
                    page: $request->get('page', 1),
                    kid1: $request->get('kid1', 0),
                    kid2: $request->get('kid2', 0),
                    sortType: $request->get('sort_type', 'default'),
                    tagId: $request->get('tag_id', 0),
                    isZb: $request->get('is_zb', 0),
                    pageSize: $request->get('page_size', 100),
                    ratio: $request->get('ratio', null),
                    classId: $request->get('class_id', 0),
                    update: $request->get('update', 0),
                    size: $request->get('size', 0),
                    fuzzy: $request->get('fuzzy', 0),
                    templateTypes: $request->get('template_type', [1, 2]),
                    templateInfo: $request->get('templ_info', []),
                    color: $request->get('color', []),
                    use: $request->get('use', 0)
                )
            );

            $response = new Response('design template index', 'DesignTemplateIndex', $items);

        } catch (UnknownPropertyException $unknownException) {

            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [],
                422);

        } catch (\Throwable $throwable) {

            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500);

        }

        return $this->response($response);
    }
}
