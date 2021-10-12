<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SearchWordSearchQuery implements QueryBuilderInterface
{
    private $query = [];
    //$type 类别 1模板2背景3元素
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $type = 1
    )
    {
    }

    public function query(): array
    {
        $this->queryKeyword();
        $this->query['bool']['must'][]['match']['type'] = $this->type;
        $this->query['bool']['filter'][]['range']['results']['gte'] = 1;
        return $this->query;
    }
    public function queryKeyword()
    {
        if ($this->keyword){
            if (mb_strlen($this->keyword) > 1) {
                $this->query['bool']['must'][]['match']['keyword'] = [
                    'query' => $this->keyword,
                    "operator" => "and"
                ];
            } else {
                $this->query['bool']['must'][]['prefix']['keyword'] = [
                    'value' => $this->keyword
                ];
            }
        }

        return $this;
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public function sortDefault()
    {
        $source = "doc['count'].value*500+doc['results'].value*1";
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
    public function getRedisKey()
    {
        $redisKey = sprintf(
            'searchword200909:%s_%d_%d',
            $this->keyword,
            $this->type,
            $this->pageSize
        );
        return $redisKey;
    }
}
