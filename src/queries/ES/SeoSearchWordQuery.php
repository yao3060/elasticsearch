<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SeoSearchWordQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    function __construct(
        public $keyword = 0,
        public $pageSize = 40
    )
    {
    }

    public function seoQuery(): array
    {
        $newQuery = $this->similarQueryKeyword($this->keyword);
        $newQuery['bool']['filter'][]['range']['count']['gte'] = '3';
        return $newQuery;
    }
    public function query(): array
    {
        if ($this->keyword) {
            $newQuery['bool']['must'][]['match']['keyword'] = $this->keyword;
        }
        $newQuery['bool']['filter'][]['range']['count']['gte'] = '3';
        return $newQuery;
    }
    public function similarQueryKeyword($keyword)
    {
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["_keyword^1", "keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        return $query;
    }
    public function getRedisKey()
    {
        $redisKey = sprintf(
            'ES_seo_search_word:%s:%s',
            date('Y-m-d'),
            $this->keyword
        );
        return $redisKey;
    }
    public function getSeoRedisKey()
    {
        $redisKey = sprintf(
            'ES_seo_similar_word:%s:%s_v10',
            $this->keyword,
            $this->pageSize
        );
        return $redisKey;
    }
}
