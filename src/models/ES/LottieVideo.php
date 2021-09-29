<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class LottieVideo extends BaseModel
{
    public static $redisDb = 8;

    public static function index()
    {
        return 'video_lottie';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'title', 'create_date', 'pr', 'width', 'height', 'class_id', 'description'];
    }

    /**
     * @param  \app\queries\ES\LottieVideoSearchQuery  $query
     * @return array
     * @throws Exception
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        \Yii::info("[LottieVideo:redisKey]:[$redisKey]", __METHOD__);

        $return = Tools::getRedis(self::$redisDb, $redisKey);

        if (!empty($return) && isset($return['hit']) && $return['hit'] && Tools::isReturnSource(
            ) === false && $query->prep != 1) {
            \Yii::info("lottie video search data source from redis", __METHOD__);
            return $return;
        }


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
            if (isset($info['hits']) && sizeof($info['hits'])) {
                $total = $info['total'] ?? 0;

                $return['hit'] = $total > 10000 ? 10000 : $total;
                foreach ($info['hits'] as $value) {
                    $return['ids'][] = $value['_id'];
                    $return['score'][$value['_id']] = $value['sort'][0] ?? [];
                }
            }
        } catch (\exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }

        Tools::setRedis(self::$redisDb, $redisKey, $return, 86400);

        return $return;
    }
}
