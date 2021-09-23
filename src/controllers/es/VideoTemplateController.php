<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\VideoTemplate;
use app\queries\ES\VideoTemplateSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

/**
 * 片段视频
 * Class VideoTemplateController
 * @package app\controllers\es
 */
class VideoTemplateController extends BaseController
{
    public function actionSearch(Request $request)
    {
        try {
            $validate = DynamicModel::validateData($request->getQueryParams(), [
                [['keyword'], 'string']
            ]);

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate params error',
                    'Validate Params Error',
                    $validate->errors,
                    422));
            }

            $validateAttributes = $validate->getAttributes();

            $search = (new VideoTemplate())->search(new VideoTemplateSearchQuery(
                keyword: $validateAttributes['keyword'] ?? "",
                classId: $validateAttributes['class_id'] ?? [],
                page: $validateAttributes['page'] ?? 1,
                pageSize: $validateAttributes['page_size'] ?? 40,
                ratio: $validateAttributes['ratio'] ?? null,
                prep: $validateAttributes['prep'] ?? 0
            ));

            return $this->response(new Response('video_template_search', 'Video Template Search', $search));

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
