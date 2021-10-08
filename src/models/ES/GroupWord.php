<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use Yii;
use yii\base\Exception;

/**
 * @package app\models\ES
 * author  ysp
 */
class GroupWord extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 8;

    public static function index()
    {
        return 'group_word';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return [
            'id',
            'title',
            'description',
            'created',
            'kid_1',
            'kid_2',
            'kid_3',
            'pr',
            'man_pr',
            'man_pr_add',
            'width',
            'height',
            'ratio',
            'scene_id',
            'is_zb'
        ];
    }

    /**
     * @param \app\queries\ES\GroupWordSearchQuery $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'GroupWord:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass by redis, redis key:' . $query->getRedisKey(), __METHOD__);
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
        } catch (Exception $e) {
            var_dump($e->getMessage());exit();
            \Yii::error($e->getMessage(), __METHOD__);
            //throw new Exception($e->getMessage());
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
