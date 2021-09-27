<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\Backend\AssetUseTop;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class Background extends BaseModel
{
    const REDIS_DB = 8;

    /**
     * @param \app\queries\ES\BackGroundSearchQuery $query
     * @return array 2021-09-06
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'Background:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            return $return;
        }
        if ($query->useCount) {
            $useInfo = AssetUseTop::getLatestBy('kid_1', 2);
        } else {
            $useInfo = '';
        }
        try {
            $info = self::find()
                ->source(['id', 'use_count'])
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
            $info['total'] = 0;
            $info['hits'] = 0;
        }
        $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
        if ($info['hits'] != 0) {
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
        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400);
        return $return;
    }


    public static function index()
    {
        return 'background2';
    }

    public static function type()
    {
        return 'list';
    }
}
