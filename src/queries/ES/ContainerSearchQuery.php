<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class ContainerSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public $keyword = 0,
        public int $page = 1,
        public int $pageSize = 40,
        public string|array $kid = '0',
    )
    {
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($this->kid) {
            $newQuery['bool']['must'][]['terms']['kid_2'] = $this->kid;
        }
        if (isset($newQuery) && $newQuery){
            return $newQuery;
        }else{
            return array();
        }

    }
    public static function queryKeyword($keyword, $is_or = false)
    {
        $operator = $is_or ? 'or' : 'and';
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["title^5", "description^1"],
            'type' => 'most_fields',
            "operator" => $operator
        ];
        return $query;
    }
    public static function sortBy()
    {
        return 'man_pr_add desc';
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
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
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        //$redis_key = "ES_container:" . ":{$keyword}_{$page}_" . implode('-', $kid) . "_{$pagesize}";
        return sprintf(
            'ES_container:%s_%d_%s_%d',
            $this->keyword,
            $this->page,
            $this->kid,
            $this->pageSize,
        );
    }
}