<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use Yii;
/**
 * @package app\models\ES
 * author  ysp
 */
class Album extends BaseModel
{
    const REDIS_DB = 8;
    const REDIS_EXPIRE = 86400;

    /**
     * @param \app\queries\ES\AlbumSearchQuery $query
     * @return array 2021-09-23
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'Album:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return && isset($return['hit']) && $return['hit'] && !Tools::isReturnSource() && $query->update != 1) {
            Yii::info('bypass by redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        try {
            $info = self::find()
                ->source(['id'])
                ->query($query->query())
                ->orderBy($query->sort())
                ->offset($query->queryOffset())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            $info['hit'] = 0;
            $info['ids'] = [];
            $info['score'] = [];
        }
        $return['total'] = $info['total'];
        $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
        foreach ($info['hits'] as $value) {
            $return['ids'][] = $value['_id'];
            $return['score'][$value['_id']] = $value['sort'][0];
        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, self::REDIS_EXPIRE);
        return $return;
    }
    public static function index()
    {
        return 'album';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes() {
        return ['id', 'url_id', 'title', '_title', 'subtitle', 'keyword', 'type', 'class_id', 'job_id', 'created', 'sort'];
    }
}
