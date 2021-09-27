<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SeoSearchWordAssetQuery implements QueryBuilderInterface
{
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $type = 1,
    )
    {
    }

    public function query(): array
    {
        $newQuery = $this->similarQueryKeyword($this->keyword, $this->type);
        return $newQuery;
    }
    public function similarQueryKeyword($keyword, $type = 1)
    {
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["_keyword^1","keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        $query['bool']['must'][]['constant_score']['filter']['term']['type'] = $type;
        return $query;
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        // $redis_key = "ES_seo_similar_word_asset:v4:{$type}:{$keyword}";
        return sprintf(
            'ES_seo_similar_word_asset:v4:%d_%s',
            $this->type,
            $this->keyword,
        );
    }
}
