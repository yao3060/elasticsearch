<?php

namespace app\models\ES;

use app\components\Tools;
use yii\base\Exception;
use app\interfaces\ES\QueryBuilderInterface;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class VideoAudio extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 8;

    public static function index()
    {
        return 'video_audio';
    }

    public static function type()
    {
        return 'list';
    }
    /**
     * @param \app\queries\ES\VideoAudioSearchQuery $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'VideoAudio:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return) {
            Yii::info('bypass redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        $info['hit'] = 0;
        $info['ids'] = [];
        $info['score'] = [];
        try {
            $info = self::find()
                ->source(['id'])
                ->query($query->query())
                ->orderBy($query->sortBy())
                ->offset($query->queryOffset())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            $return['hit'] = ($info['total'] ?? 0)  > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400);
            return $return;
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return $info;
        }
    }
}
