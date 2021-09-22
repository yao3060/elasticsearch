<?php

namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\Template;
use app\queries\ES\TemplateRecommendSearchQuery;
use app\queries\ES\TemplateSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class TemplateController extends BaseController
{
    /**
     * 搜索模板
     * @param Request $request
     * @return \yii\web\Response
     */
    public function actionSearch(Request $request)
    {
        try {
            $template = new Template();

            $validate = DynamicModel::validateData($request->getBodyParams(), $template->rules());

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate params error',
                    'Validate Params Error',
                    $validate->errors,
                    422));
            }

            $validateAttributes = $validate->getAttributes();

            $search = $template->search(new TemplateSearchQuery(
                keyword: $validateAttributes['keyword'] ?? null,
                page: $validateAttributes['page'] ?? 1,
                kid1: $validateAttributes['kid1'] ?? 0,
                kid2: $validateAttributes['kid2'] ?? 0,
                sortType: $validateAttributes['sort_type'] ?? 'default',
                tagId: $validateAttributes['tag_id'] ?? 0,
                isZb: $validateAttributes['is_zb'] ?? 1,
                pageSize: $validateAttributes['page_size'] ?? 35,
                ratio: $validateAttributes['ratio'] ?? null,
                classId: $validateAttributes['class_id'] ?? 0,
                update: $validateAttributes['update'] ?? 0,
                size: $validateAttributes['size'] ?? 0,
                fuzzy: $validateAttributes['fuzzy'] ?? 0,
                templateTypes: $validateAttributes['template_type'] ?? [1, 2],
                use: $validateAttributes['use'] ?? 0,
                color: $validateAttributes['color'] ?? [],
                width: $validateAttributes['width'] ?? 0,
                height: $validateAttributes['height'] ?? 0,
                classIntersectionSearch: $validateAttributes['class_intersection_search'] ?? 0,
                elasticsearchColor: $validateAttributes['elasticsearch_color'] ?? ''
            ));

            $response = new Response('es_template_search', 'ESTemplate Search', $search);

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

    /**
     * 搜索推荐模板
     * @param Request $request
     * @return \yii\web\Response
     */
    public function actionRecommendSearch(Request $request)
    {
        try {

            $template = new Template();

            $validate = DynamicModel::validateData($request->getBodyParams(), $template->recommendRules());

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate_param_errors',
                    'Validate Param Errors',
                    $validate->errors,
                    422));
            }

            $attributes = $validate->getAttributes();

            $recommendSearch = $template->recommendSearch(new TemplateRecommendSearchQuery(
                keyword: $attributes['keyword'] ?? 0,
                page: $attributes['page'] ?? 1,
                pageSize: $attributes['page_size'] ?? 40,
                templateType: $attributes['template_type'] ?? null,
                ratio: $attributes['ratio'] ?? null
            ));

            $response = new Response(
                'es_template_commend_search',
                'ESTemplate Commend Search',
                $recommendSearch);

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
