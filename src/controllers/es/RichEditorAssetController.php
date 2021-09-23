<?php
namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\RichEditorAsset;
use app\queries\ES\RichEditorAssetSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class RichEditorAssetController extends BaseController
{
    public function actionSearch(Request $request)
    {
        try {

            $validate = DynamicModel::validateData($request->getQueryParams(), [
                ["keyword", "string"],
                [["page"], "integer"]
            ]);

            if ($validate->hasErrors()) {
                return $this->response(new Response(
                    'validate params error',
                    'Validate Params Error',
                    $validate->errors,
                    422));
            }

            $validateAttributes = $validate->getAttributes();

            $search = (new RichEditorAsset())->search(new RichEditorAssetSearchQuery(
                keyword: $validateAttributes['keyword'] ?? 0,
                classId: $validateAttributes['class_id'] ?? [],
                page: $validateAttributes['page'] ?? 1,
                pageSize: $validateAttributes['page_size'] ?? 40,
                ratio: $validateAttributes['ratio'] ?? 0
            ));

            return $this->response(new Response('Rt Asset Search', 'rt_asset_search', $search));

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
