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
class Picture extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

    public static function index()
    {
        return 'picture2';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'title', 'description', 'created', 'kid_1', 'kid_2', 'kid_3', 'pr', 'man_pr', 'man_pr_add', 'width', 'height', 'ratio', 'scene_id', 'is_vip_asset'];
    }

    /**
     * @param \app\queries\ES\PictureSearchQuery $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分 数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis($this->redisDb, $query->getRedisKey());
        $log = 'Picture:redisKey:' . $query->getRedisKey();
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
                //$return['is_vip_asset'][$value['_id']] = $value['is_vip_asset'];
                $return['is_vip_asset'][$value['_id']] = 0;
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            Tools::setRedis($this->redisDb, $query->getRedisKey(), $return, 86400);
            return $return;
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            return $return;
        }
    }
}
