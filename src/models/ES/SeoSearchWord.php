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
class SeoSearchWord extends BaseModel
{
    const REDIS_DB  = 8;

    /**
     * @param \app\queries\ES\SeoSearchWordQuery $query
     * @return array 2021-09-07
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'SeoSearchWord:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        try {
            $info = self::find()
                ->source(['id', 'keyword'])
                ->query($query->query())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            if ($info['total'] > 0) {
                $return['is_seo_search_keyword'] = true;
                $return['id'] = $info['hits'][0]['_id'];
                $return['keyword'] = $query->keyword;
            }
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400);
            if (empty($return)) {
                $return = [];
            }
            return $return;
        } catch (Exception $e) {
            $return['is_seo_search_keyword'] = false;
            return $return;
        }
    }

    public function seoSearch(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getSeoRedisKey());
        if (!$return) {
            try {
                $info = self::find()
                    ->source(['id', '_keyword', 'pinyin'])
                    ->query($query->seoQuery())
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (Exception $e) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
            }
            if ($info['total'] > 0) {
                foreach ($info['hits'] as $k => $v) {
                    $return[$k]['id'] = $v['_source']['id'];
                    $return[$k]['keyword'] = $v['_source']['_keyword'];
                    $return[$k]['pinyin'] = $v['_source']['pinyin'];
                }
            }
            Tools::setRedis(self::REDIS_DB, $query->getSeoRedisKey(), $return, 86400 * 30);
        }
        return $return;
    }



    public static function index()
    {
        return 'seo_search_word';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'keyword', '_keyword', 'pinyin', 'count'];
    }
}
