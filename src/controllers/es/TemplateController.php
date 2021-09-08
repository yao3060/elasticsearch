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

            $search = $template->search(new TemplateSearchQuery($validate->getAttributes()));

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

            $recommendSearch = $template->recommendSearch(new TemplateRecommendSearchQuery($validate->getAttributes()));

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
