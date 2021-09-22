<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class Seo extends BaseModel
{
    private $redisDb = 8;

    /**
     * @param \app\queries\ES\SeoSearchQuery $query
     * @return array 2021-09-07
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis($this->redisDb, $query->getRedisKey());
        if ($return && isset($return['hit']) && $return['hit']) {
            return $return;
        }
        try {
            $info = self::find()
                ->source(['id', 'keyword'])
                ->query($query->query())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            $info['total'] = 0;
            $return['is_seo_search_keyword'] = false;
        }
        if ($info['total'] > 0) {
            $return['is_seo_search_keyword'] = true;
            $return['id'] = $info['hits'][0]['_id'];
            $return['keyword'] = $query->keyword;
        }
        Tools::setRedis($this->redisDb, $query->getRedisKey(), $return, 86400);
        return $return;
    }

    public function seoSearch(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis($this->redisDb, $query->getSeoRedisKey());
        if (!$return) {
            try {
                $info = self::find()
                    ->source(['id', '_keyword', 'pinyin'])
                    ->query($query->seoQuery())
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\exception $e) {
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
            Tools::setRedis($this->redisDb, $query->getSeoRedisKey(), $return, 86400 * 30);
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
