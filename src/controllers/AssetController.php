<?php

/**
 * 重构ES,Asset搜索方法
 */
namespace app\controllers;
use app\models\ES\Asset;
use app\queries\ES\AssetSearchQuery;
use yii\rest\Controller;
use Yii;

class AssetController extends Controller
{
    public function actionSearch()
    {
        $data = Yii::$app->request->post();
        $data = (new Asset())
            ->search(new AssetSearchQuery($data['keyword'], $data['page'], $data['pageSize'],$data['sceneId'],$data['isZb'],$data['sort'],$data['useCount']));
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
    public function actionRecommendSearch()
    {
        $data = Yii::$app->request->post();
        $data = (new Asset())
            ->recommendSearch(new AssetSearchQuery($data['keyword'], $data['page'], $data['pageSize']));
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
    public function actionSaveRecord()
    {
        $data = Yii::$app->request->post();
        $data = (new Asset())
            ->saveRecord($data);
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
}
