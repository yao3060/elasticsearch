<?php
namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\Template;
use app\queries\ES\TemplateRecommendSearchQuery;
use app\queries\ES\TemplateSearchQuery;
use yii\base\DynamicModel;
use yii\base\Exception;
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
        $response = new Response();

        try {

            $template = new Template();

            // 验证
            $validate = DynamicModel::validateData($request->get(), $template->rules());

            if ($validate->hasErrors()) {
                throw new Exception($validate->errors);
            }

            $search = $template->search(new TemplateSearchQuery($validate->getAttributes()));
            $response->code('es_template_search');
            $response->message('ESTemplate Search');
            $response->data($search);

        } catch(UnknownPropertyException $unknownException) {

            $response->status(422);
            $response->code(StringHelper::snake($unknownException->getName()));
            $response->message(StringHelper::replaceModelName($unknownException->getMessage()));

        } catch (\Throwable $throwable) {

            $response->status(500);
            $response->code('Internal Server Error');
            $response->message($throwable->getMessage());
            $response->data(YII_DEBUG ?  explode("\n", $throwable->getTraceAsString()) : []);

        }

        return $this->response($response);
    }

    /**
     * 保存模板
     * @param Request $request
     */
    public function actionStore(Request $request)
    {
        $response = new Response();

        try {

            Template::saveRecord(['id' => 3225970]);

        } catch (UnknownPropertyException $unknownException) {

            $response->status(422);
            $response->code(StringHelper::snake($unknownException->getName()));
            $response->message(StringHelper::replaceModelName($unknownException->getMessage()));

        } catch (\Throwable $throwable) {

            $response->status(500);
            $response->code('Internal Server Error');
            $response->message($throwable->getMessage());
            $response->data(YII_DEBUG ?  explode("\n", $throwable->getTraceAsString()) : []);

        }

        $response->data([
            'mason' => 1
        ]);

        return $this->response($response);
    }

    /**
     * 搜索模板
     * @param Request $request
     * @return \yii\web\Response
     */
    public function actionRecommendSearch(Request $request)
    {
        $response = new Response();

        try {

            $template = new Template();

            $validate = DynamicModel::validateData($request->get(), $template->recommendRules());

            if ($validate->hasErrors()) {
                throw new Exception($validate->errors);
            }

            $recommendSearch = $template->recommendSearch(new TemplateRecommendSearchQuery($validate->getAttributes()));

            $response->code('es_template_commend_search');

            $response->message('ESTemplate Commend Search');

            $response->data($recommendSearch);

        } catch (UnknownPropertyException $unknownException) {

            $response->status(422);
            $response->code(StringHelper::snake($unknownException->getName()));
            $response->message(StringHelper::replaceModelName($unknownException->getMessage()));

        } catch (\Throwable $throwable) {

            $response->status(500);
            $response->code('Internal Server Error');
            $response->message($throwable->getMessage());
            $response->data(YII_DEBUG ?  explode("\n", $throwable->getTraceAsString()) : []);

        }

        return $this->response($response);

    }
}
