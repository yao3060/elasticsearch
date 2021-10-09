<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class Container extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 8;

    public static function index()
    {
        return 'container';
    }
    public static function type()
    {
        return 'list';
    }
    public function attributes()
    {
        return ['id', 'title', 'description', 'created', 'kid_1', 'kid_2', 'kid_3', 'pr', 'man_pr', 'man_pr_add', 'width', 'height', 'ratio', 'scene_id'];
    }
    /**
     * @param \app\queries\ES\ContainerSearchQuery $query
     * @return array 2021-09-15
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'Container:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass redis, redis key:' . $query->getRedisKey(), __METHOD__);
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
            $return['hit'] = $info['total'] ?? 0 > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 126000 + rand(-3600, 3600));
            return $return;
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return $return;
        }
    }
}
