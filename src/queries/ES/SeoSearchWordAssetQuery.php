<?php


namespace app\queries\ES;

class SeoSearchWordAssetQuery extends BaseTemplateSearchQuery
{
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $type = 1,
    ) {
    }

    public function query(): array
    {
        $this->similarQueryKeyword();

        return $this->query;
    }

    public function similarQueryKeyword()
    {
        $this->query['bool']['must'][]['multi_match'] = [
            'query' => $this->keyword,
            'fields' => ["_keyword^1", "keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];

        $this->query['bool']['must'][]['constant_score']['filter']['term']['type'] = $this->type;
    }

    public function pageSizeSet()
    {
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public function getRedisKey()
    {
        // $redis_key = "ES_seo_similar_word_asset:v4:{$type}:{$keyword}";
        return sprintf(
            'ES_seo_similar_word_asset:v4:%d_%s',
            $this->type,
            $this->keyword,
        );
    }
}
