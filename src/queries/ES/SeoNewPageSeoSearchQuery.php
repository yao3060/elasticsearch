<?php


namespace app\queries\ES;


class SeoNewPageSeoSearchQuery extends BaseTemplateSearchQuery
{
    public function __construct(
        public $keyword = 0,
        public $pageSize = 10
    )
    {}

    public function queryKeyword()
    {
        $this->query['bool']['must'][]['multi_match'] = [
            'query' => $this->keyword,
            'fields' => ["_keyword^1","keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        return $this;
    }

    public function query(): array
    {
        $this->queryKeyword();

        return $this->query;
    }

    public function getRedisKey()
    {
        $redisKey = "ES_seo_new_page:".date('Y-m').":{$this->keyword}_{$this->pageSize}_v3";

        return $redisKey;
    }
}
