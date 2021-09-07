<?php

/**
 * 重构ES,background搜索方法
 */
namespace app\controllers\es;
use app\models\ES\Background;
use app\queries\ES\BackGroundSearchQuery;
use yii\rest\Controller;
use Yii;

class BackgroundController extends Controller
{
    /**
     * @return \yii\web\Response
     */
    public function actionSearch()
    {
        $data = Yii::$app->request->post();
        $data = (new Background())
            ->search(new BackGroundSearchQuery($data['keyword'], $data['page'], $data['pageSize'],
                $data['sceneId'] ?? 0,$data['isZb'] ?? 0,$data['sort'] ?? 0,$data['useCount'] ?? 0,$data['kid'] ?? 0,
                $data['ratioId'] ?? 0,$data['class'] ?? 0 ,$data['isBg'] ?? 0));
        $this->response->headers->set('X-Total', 1000);
        return $this->asJson($data);
    }
}
