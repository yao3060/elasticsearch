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
    private $redisDb = 8;

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
        $return = Tools::getRedis($this->redisDb, $query->getRedisKey());
        $log = 'SeoDetailKeywordForTitle:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
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
        }
        if ($info['total'] > 0) {
            foreach ($info['hits'] as $k => $v) {
                $return[$k]['id'] = $v['_id'];
                $return[$k]['keyword'] = $v['_source']['_keyword'];
            }
        }
        Tools::setRedis($this->redisDb, $query->getRedisKey(), $return, 86400 * 30);
        return $return;
    }

}
