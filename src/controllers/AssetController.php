<?php

/**
 * 重构ES,Asset搜索方法
 */
namespace app\controllers;
use app\models\ES\Asset;
use app\queries\ES\AssetSearchQuery;
use yii\web\Controller;

class AssetController extends Controller
{
    public function actionIndex()
    {
        $data = (new Asset())
            ->search(new AssetSearchQuery('red', 2, [], [], [], 100));
        return json_encode($data);
    }
}
