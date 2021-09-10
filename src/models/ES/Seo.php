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
     * @param QueryBuilderInterface $query
     * @return array 2021-09-07
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = sprintf('ES_asset2:%s:%s',date('Y-m-d'),$query->keyword);
        $return = Tools::getRedis($this->redisDb, $redisKey);
        if (!$return) {
            unset($return);
            if ($query->keyword) {
                $newQuery['bool']['must'][]['match']['keyword'] = $query->keyword;
            }
            $newQuery['bool']['filter'][]['range']['count']['gte'] = '3';
            try {
                $info = self::find()
                    ->source(['id', 'keyword'])
                    ->query($newQuery)
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
            Tools::setRedis($this->redisDb, $redisKey, $return, 86400);
        }
        return $return;
    }
    public function seoSearch(QueryBuilderInterface $query): array{
        $redisKey = sprintf('ES_seo_similar_word:%s:%s_v10',$query->keyword,$query->pageSize);
        $return = Tools::getRedis($this->redisDb, $redisKey);
        if (!$return) {
            $newQuery = $this->similarQueryKeyword($query->keyword);
            $newQuery['bool']['filter'][]['range']['count']['gte'] = '3';
            try {
                $info = self::find()
                    ->source(['id', '_keyword','pinyin'])
                    ->query($newQuery)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\exception $e) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
            }
            if ($info['total'] > 0) {
                foreach ($info['hits'] as $k=>$v) {
                    $return[$k]['id'] = $v['_source']['id'];
                    $return[$k]['keyword'] = $v['_source']['_keyword'];
                    $return[$k]['pinyin'] = $v['_source']['pinyin'];
                }

            }
            Tools::setRedis($this->redisDb, $redisKey, $return, 86400 * 30);
        }
        return $return;
    }
    public static function similarQueryKeyword($keyword) {
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["_keyword^1","keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        return $query;
    }
    public static function index() {
        return 'seo_search_word';
    }

    public static function type() {
        return 'list';
    }

    public function attributes() {
        return ['id', 'keyword', '_keyword','pinyin','count'];
    }
}
