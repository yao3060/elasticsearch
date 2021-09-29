<?php


namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\LottieVideo;
use app\queries\ES\LottieVideoSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class LottieVideoController extends BaseController
{
    public function actionSearch(Request $request)
    {
        try {
            $validate = DynamicModel::validateData(
                $request->getQueryParams(),
                [
                    [['keyword'], 'string']
                ]
            );

            if ($validate->hasErrors()) {
                return $this->response(
                    new Response(
                        'validate params error',
                        'Validate Params Error',
                        $validate->errors,
                        422
                    )
                );
            }

            $validateAttributes = $validate->getAttributes();

            $search = (new LottieVideo())->search(
                new LottieVideoSearchQuery(
                    keyword: $validateAttributes['keyword'] ?? 0,
                    classId: $validateAttributes['class_id'] ?? [],
                    page: $validateAttributes['page'] ?? 1,
                    pageSize: $validateAttributes['page_size'] ?? 40,
                    prep: $validateAttributes['prep'] ?? 0
                )
            );

            $response = new Response('lottie_video_search', 'Lottie Video Search', $search);
        } catch (UnknownPropertyException $unknownException) {
            $response = new Response(
                StringHelper::snake($unknownException->getName()),
                StringHelper::replaceModelName($unknownException->getMessage()),
                [],
                422
            );
        } catch (\Throwable $throwable) {
            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage() . $throwable->getFile() . $throwable->getLine(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500
            );
        }

        return $this->response($response);
    }
}
