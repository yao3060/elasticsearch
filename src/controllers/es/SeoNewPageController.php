<?php


namespace app\controllers\es;

use app\components\Response;
use app\controllers\BaseController;
use app\models\ES\SeoNewPage;
use app\queries\ES\SeoNewPageSeoSearchQuery;
use yii\base\DynamicModel;
use yii\web\Request;

class SeoNewPageController extends BaseController
{
    public function actionSeoSearch(Request $request)
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
            $search = (new SeoNewPage())->seoSearch(
                new SeoNewPageSeoSearchQuery(
                    keyword: $validateAttributes['keyword'] ?? 0,
                    pageSize: $validateAttributes['page_size'] ?? 10
                )
            );

            $response = new Response('seo_new_page_list', 'SeoNewPageList', $search);
        } catch (\Throwable $throwable) {
            $response = new Response(
                'Internal Server Error',
                $throwable->getMessage(),
                YII_DEBUG ? explode("\n", $throwable->getTraceAsString()) : [],
                500
            );
        }

        return $this->response($response);
    }
}
