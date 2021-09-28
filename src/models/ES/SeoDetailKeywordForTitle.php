<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
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
        try {
            $info = self::find()
                ->source(['id', '_keyword'])
                ->query($query->query())
                ->limit(2)
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            $info['total'] = 0;
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }
        if ($info['total'] > 0) {
            foreach ($info['hits'] as $k => $v) {
                $return[$k]['id'] = $v['_id'];
                $return[$k]['keyword'] = $v['_source']['_keyword'];
            }
        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400 * 30);
        return $return;
    }

}
