<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class LottieVideoWord extends BaseModel
{
    public static $redisDb = 8;

    public static function index()
    {
        return 'video_lottie_word';
    }


    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'title', 'create_date', 'pr', 'width', 'height', 'description'];
    }

    public function search(QueryBuilderInterface $query) :array
    {
        $return = Tools::getRedis(self::$redisDb, $query->getRedisKey());

        if (!$return || Tools::isReturnSource() || $query->prep) {
            unset($return);

            $return['hit'] = 0;
            $return['ids'] = [];
            $return['score'] = [];

            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($query->query())
                    ->orderBy($query->sort)
                    ->offset($query->offset)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\exception $e) {
                throw new Exception($e->getMessage());
            }

            if (isset($info['hits']) && sizeof($info['hits'])) {
                $total = $info['total'] ?? 0;
                $return['hit'] = $total > 10000 ? 10000 : $total;
                foreach ($info['hits'] as $value) {
                    $return['ids'][] = $value['_id'];
                    $return['score'][$value['_id']] = $value['sort'][0];
                }
            }

//            Tools::setRedis(self::$redisDb, $query->getRedisKey(), $return, 86400);
        }
        return $return;
    }
}
