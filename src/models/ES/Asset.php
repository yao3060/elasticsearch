<?php

namespace app\models\ES;

use Yii;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\Backend\AssetUseTop;
use yii\base\Exception;

/**
 * Class Asset
 * @package app\models\ES
 * author  ysp
 */
class Asset extends BaseModel
{
    const REDIS_DB = 8;
    const REDIS_EXPIRE = 86400;

    /**
     * @param \app\queries\ES\AssetSearchQuery $query
     * @return array 2021-09-03
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();
        $log = 'Asset:redisKey:' . $redisKey;
        yii::info($log, __METHOD__);
        $return = Tools::getRedis(self::REDIS_DB, $redisKey);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass by redis, redis key:' . $redisKey, __METHOD__);
            return $return;
        }
        if ($query->useCount) {
            $useInfo = AssetUseTop::getLatestBy('kid_1', 1);
        } else {
            $useInfo = '';
        }
        $return['hit'] = 0;
        $return['ids'] = [];
        $return['score'] = [];
        try {
            $info = self::find()
                ->source(['id', 'use_count'])
                ->query($query->query())
                ->orderBy($query->sortBy())
                ->offset($query->queryOffset())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }
        $return['hit'] = $info['total'] ?? 0 > 10000 ? 10000 : $info['total'];
        foreach ($info['hits'] as $value) {
            $return['ids'][] = $value['_id'];
            $return['score'][$value['_id']] = $value['sort'][0];
            if ($useInfo) {
                if ($value['_source']['use_count'] >= $useInfo['top1_count']) {
                    $return['use_count'][$value['_id']] = 1;
                } elseif ($value['_source']['use_count'] >= $useInfo['top5_count']) {
                    $return['use_count'][$value['_id']] = 2;
                } else {
                    $return['use_count'][$value['_id']] = 3;
                }
            }
        }
        Tools::setRedis(self::REDIS_DB, $redisKey, $return, self::REDIS_EXPIRE);
        return $return;
    }

    public static function index()
    {
        return 'asset2';
    }
    public static function type()
    {
        return 'list';
    }
}
