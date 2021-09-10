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
                    keyword: $request->getBodyParam('keyword', 0),
                    page: $request->getBodyParam('page', 1),
                    kid1: $request->getBodyParam('kid1', 0),
                    kid2: $request->getBodyParam('kid2', 0),
                    sortType: $request->getBodyParam('sort_type', 'default'),
                    tagId: $request->getBodyParam('tag_id', 0),
                    isZb: $request->getBodyParam('is_zb', 0),
                    pageSize: $request->getBodyParam('page_size', 100),
                    ratio: $request->getBodyParam('ratio', null),
                    classId: $request->getBodyParam('class_id', 0),
                    update: $request->getBodyParam('update', 0),
                    size: $request->getBodyParam('size', 0),
                    fuzzy: $request->getBodyParam('fuzzy', 0),
                    templateTypes: $request->getBodyParam('template_type', [1, 2]),
                    templateInfo: $request->getBodyParam('templ_info', []),
                    color: $request->getBodyParam('color', []),
                    use: $request->getBodyParam('use', 0)
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
