<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class GifAsset extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 8;

    public static function index()
    {
        return 'gif_asset';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'title', 'description', 'create_date', 'pr', 'width', 'height', 'class_id', 'is_zb', 'size_w380'];
    }
    /**
     * @param \app\queries\ES\GifAssetSearchQuery $query
     * @return array 2021-09-17
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'GifAsset:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return) {
            return $return;
        }
        $return['hit'] = 0;
        $return['ids'] = [];
        $return['score'] = [];
        try {
            $info = self::find()
                ->source(['id'])
                ->query($query->query())
                ->orderBy($query->sortBy())
                ->offset($query->queryOffset())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }
        $return['hit'] = $info['total'] ?? 0 > 10000 ? 10000 : $info['total'];
        foreach ($info['hits'] as $value) {
            $return['ids'][] = $value['_id'];
            $return['score'][$value['_id']] = $value['sort'][0];
        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400);
        return $return;
    }
}
