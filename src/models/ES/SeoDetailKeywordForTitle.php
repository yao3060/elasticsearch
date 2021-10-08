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
class SeoDetailKeywordForTitle extends BaseModel
{
    /**
     * @var int  redis
     */
    const REDIS_DB = 8;

    public static function index()
    {
        return 'seo_detail_keyword_for_title';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'keyword', '_keyword', 'count', 'use'];
    }

    /**
     * @param \app\queries\ES\SeoDetailKeywordForTitleQuery $query
     * @return array 2021-09-16
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function Search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'SeoDetailKeywordForTitle:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass by redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        $repsonseData = [];
        try {
            $info = self::find()
                ->source(['id', '_keyword'])
                ->query($query->query())
                ->limit(2)
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            $total = $info['total'] ?? 0;
            if ($total > 0 && isset($info['hits']) && $info['hits']) {
                foreach ($info['hits'] as $v) {
                    $repsonseData[] = [
                        'id' => $v['_id'] ?? 0,
                        'keyword'=> $v['_source']['_keyword'] ?? ''
                    ];
                }
            }
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $repsonseData, 86400 * 30);
            return $repsonseData;
        } catch (Exception $e) {

            \Yii::error($e->getMessage(), __METHOD__);
            return $repsonseData;
        }

    }

}
