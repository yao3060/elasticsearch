<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class ContainerSearchQuery implements QueryBuilderInterface
{
    private $query = [];
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $kid = '0',
    ) {
    }

    public function query(): array
    {
        $this->queryKeyword();
        if ($this->kid) {
            $this->query['bool']['must'][]['terms']['kid_2'] = $this->kid;
        }
        return $this->query;
    }
    public function queryKeyword($is_or = false)
    {
        if ($this->keyword){
            $operator = $is_or ? 'or' : 'and';
            $this->query['bool']['must']['terms']['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^5", "description^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }
        return $this;
    }
    public function sortBy()
    {
        return 'man_pr_add desc';
    }
    public function pageSizeSet()
    {
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
        //$redis_key = "ES_container:" . ":{$keyword}_{$page}_" . implode('-', $kid) . "_{$pagesize}";
        $kid = $this->kid ? $this->kid : [];
        if (!is_array($kid)) {
            $kid = [$kid];
        }
        return sprintf(
            'ES_container:%s_%d_%s_%d',
            $this->keyword,
            $this->page,
            implode('-', $kid),
            $this->pageSize,
        );
    }
}
