<?php

/**
 * 重构ES,SeoSearch搜索模块
 */
namespace app\controllers\es;
use app\models\ES\Seo;
use app\queries\ES\SeoSearchQuery;
use yii\rest\Controller;
use Yii;

class SeoController extends Controller
{
    public function actionSearch()
    {
        $data = Yii::$app->request->post();
        $data = (new Seo())
            ->search(new SeoSearchQuery($data['keyword']));
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
    public function actionSeoSearch()
    {
        $data = Yii::$app->request->post();
        $data = (new Seo())
            ->seoSearch(new SeoSearchQuery($data['keyword'], $data['pageSize']));
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
}
