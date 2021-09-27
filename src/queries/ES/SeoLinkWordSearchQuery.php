<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\services\designers\DesignerRecommendAssetTagService;

class SeoLinkWordSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
    )
    {
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery['bool']['must'][]['match']['keyword'] = $this->keyword;
        }else{
            $newQuery = '';
        }
        return $newQuery;
    }
    public  function similarQueryKeyword($keyword) {
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["_keyword^1","keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        return $query;
    }
    public function seoQuery(){
        $newQuery = $this->similarQueryKeyword($this->keyword);
        return $newQuery;
    }

    public function getRedisKey()
    {
        //$redis_key = "ES_seo_search_word:" . date('Y-m-d') . ":{$keyword}";
        return sprintf(
            'ES_seo_search_word:%s:%s',
            date('Y-m-d'),
            $this->keyword,
        );
    }
    public function getSeoRedisKey()
    {
        //$redis_key = "ES_seo_link_word:{$keyword}:v6";;
        return sprintf(
            'ES_seo_link_word:%s:v6',
            $this->keyword,
        );
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public function sort()
    {
        $sort = $this->sortDefault();
        return $sort;
    }
    public function sortDefault() {
        $source = "(int)(_score)";
        $sort['_script'] = [
            'type' => 'number',
            'script' => [
                "lang" => "painless",
                "source" => $source
            ],
            'order' => 'desc'
        ];
        return $sort;
    }
    public function queryOffset()
    {
        if ($this->page * $this->pageSize > 10000) {
            $this->pageSize = $this->pageSize - ($this->page * $this->pageSize - 10000) % $this->pageSize;
            $offset = 10000 - $this->pageSize;
        } else {
            $offset = ($this->page - 1) * $this->pageSize;
        }
        return $offset;
    }
}
