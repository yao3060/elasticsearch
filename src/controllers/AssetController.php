<?php

/**
 * 重构ES,Asset搜索方法
 */
namespace app\controllers;
use app\models\ES\Asset;
use app\queries\ES\AssetSearchQuery;
use yii\rest\Controller;

class AssetController extends Controller
{
    public function actionIndex()
    {
        $data = (new Asset())
            ->search(new AssetSearchQuery('red', 1, 40, [], 1, 100));
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
}
