<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class VideoAudio extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

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
        $return = Tools::getRedis($this->redisDb, $query->getRedisKey());
        if (!$return || $query->prep) {
            unset($return);
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
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
            }
            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            Tools::setRedis($this->redisDb, $query->getRedisKey(), $return, 86400);
        }
        return $return;
    }
}
