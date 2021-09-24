<?php


namespace app\controllers\es;


use app\components\Response;
use app\controllers\BaseController;
use app\helpers\StringHelper;
use app\models\ES\LottieVideoWord;
use app\queries\ES\LottieVideoWordSearchQuery;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;

class LottieVideoWordController extends BaseController
{
    public function actionSearch(Request $request)
    {
        try {

            $validate = DynamicModel::validateData($request->getQueryParams(), []);

            if ($validate->hasErrors()) {
                if ($validate->hasErrors()) {
                    return $this->response(new Response(
                        'validate params error',
                        'Validate Params Error',
                        $validate->errors,
                        422));
                }
            }

            $validateAttributes = $validate->getAttributes();

            $search = (new LottieVideoWord())->search(new LottieVideoWordSearchQuery(
                keyword: $validateAttributes['keyword'] ?? 0,
                page: $validateAttributes['page'] ?? 1,
                pageSize: $validateAttributes['page_size'] ?? 40,
                prep: $validateAttributes['prep'] ?? 0
            ));

            $response = new Response('lottie_video_word_search', 'Lottie Video Word Search', $search);

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
