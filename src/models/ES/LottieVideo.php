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

        $responseData = [
            'hit' => 0,
            'ids' => [],
            'score' => []
        ];

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

                $responseData['hit'] = $total > 10000 ? 10000 : $total;
                foreach ($info['hits'] as $value) {
                    $responseData['ids'][] = $value['_id'] ?? 0;
                    $responseData['score'][$value['_id']] = $value['sort'][0] ?? [];
                }
            }
        } catch (Exception $e) {
            \Yii::error("LottieVideo Model Error: " . $e->getMessage(), __METHOD__);
        }

        Tools::setRedis(self::$redisDb, $redisKey, $responseData, 86400);

        return $responseData;
    }
}
