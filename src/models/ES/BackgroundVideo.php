<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

class BackgroundVideo extends BaseModel
{
    public static $redisDb = 8;

    public static function validateRules()
    {
        return [
        ];
    }

    public static function index()
    {
        return 'bg_video';
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
     * 搜索背景视频
     * @param  \app\queries\ES\BackgroundVideoQuery  $query
     * @return array
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        \Yii::info("[DesignerTemplate:redisKey]:[$redisKey]", __METHOD__);
        $return = Tools::getRedis(self::$redisDb, $redisKey);

        if (!empty($return) && Tools::isReturnSource() === false) {
            \Yii::info("background video search data source from redis", __METHOD__);
            return $return;
        }

        $return = [
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

            $total = $info['total'] ?? 0;

            $return['hit'] = $total > 10000 ? 10000 : $total;
            if (isset($info['hits']) && sizeof($info['hits'])) {
                foreach ($info['hits'] as $value) {
                    $return['ids'][] = $value['_id'] ?? 0;
                    $return['score'][$value['_id']] = $value['sort'][0] ?? [];
                }
            }
        } catch (\Throwable $throwable) {
            \Yii::error("BackgroundVideo Model Error: " . $throwable->getMessage(), __METHOD__);
        }

        Tools::setRedis(self::$redisDb, $redisKey, $return, 86400);

        return $return;
    }
}
