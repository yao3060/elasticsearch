<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class AlbumSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 5,
        public $classId = '',
        public $type = 2,
        public $sortType = 'default',
        public $update = 0,
        public $fuzzy = 0,
    )
    {
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword, $this->fuzzy);
        }
        if ($this->type) {
            $newQuery['bool']['must'][]['terms']['type'] = $this->type;
            //$newQuery['bool']['must'][]['match']['type'] = $this->type;
        }
        if ($this->classId) {
            $class_id = explode('_', $this->classId);
            foreach ($class_id as $key) {
                if ($key > 0) {
                    $newQuery['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }
        return $newQuery;
    }
    public function queryKeyword($keyword, $fuzzy = 0) {
        $operator = $fuzzy ? 'or' : 'and';
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["title^1", "subtitle^1", "keyword^1"],
            'type' => 'most_fields',
            "operator" => $operator
        ];
        return $query;
    }

    public function getRedisKey()
    {
        $classId = $this->classId ? $this->classId : '0_0_0';
        $redisKey = sprintf(
            'ES_album03-20:%s_%s_%s_%s_%d_%d_%d',
            date('Y-m-d'),
            $this->keyword,
            $this->sortType,
            $classId,
            $this->type,
            $this->pageSize,
            $this->page
        );
        if ($this->fuzzy == 1){
            $redisKey .= ":fuzzy";
        }
        return $redisKey;
    }
    public function pageSizeSet()
    {
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public function sort()
    {
        return 'sort desc';
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
