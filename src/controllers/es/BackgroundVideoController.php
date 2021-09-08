<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\BackgroundVideo;
use app\queries\ES\BackgroundVideoQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class BackgroundVideoController extends BaseController
{
    public function actionSearch(Request $request)
    {
        try {

            $validate = DynamicModel::validateData($request->getBodyParams(), BackgroundVideo::validateRules());

            if ($validate->hasErrors()) {
                return new Response('validate param errors', 'Validate Param Errors', [], 422);
            };

            $search = (new BackgroundVideo())->search(new BackgroundVideoQuery($validate->getAttributes()));

            $response = new Response('background_video_search', 'Background Video Search', $search);

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
