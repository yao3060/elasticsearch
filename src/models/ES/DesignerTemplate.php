<?php

namespace app\models\ES;

use Yii;
use app\interfaces\ES\QueryBuilderInterface;
use yii\elasticsearch\Query;

class DesignerTemplate extends BaseModel
{
  const REDIS_DB = '_search';

  public static function getDb()
  {
    return Yii::$app->get('elasticsearch_second');
  }


  public function search(QueryBuilderInterface $query): array
  {
    $return = [];
    try {
      if ($query->color) {
        $info =  (new Query())->from('818ps_pic', '818ps_pic')
          ->source(['templ_id'])
          ->query($query->query())
          ->offset($query->offset)
          ->limit($query->pageSize)
          ->createCommand(Yii::$app->get('elasticsearch_color'))
          ->search([], ['track_scores' => true])['hits'];
      } else {

        $info = self::find()
          ->source(['temple_id'])
          ->query($query->query())
          ->orderBy($query->sort)
          ->offset($query->offset)
          ->limit($query->pageSize)
          ->createCommand()
          ->search([], ['track_scores' => true])['hits'];
      }

      $return['total'] = $info['total'];
      $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
      foreach ($info['hits'] as $value) {
        $return['ids'][] = $value['_id'];
        $return['score'][$value['_id']] = $value['sort'][0];
      }
    } catch (\Exception $e) {
      $return['hit'] = 0;
      $return['ids'] = [];
      $return['score'] = [];
    }


    // if (!IpsAuthority::check(IOS_ALBUM_USER)) {
    //   Tools::setRedis(self::$redis_db, $redis_key, $return, 86400 + rand(-3600, 3600));
    // }

    return  $return;
  }
}
