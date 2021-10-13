<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class AlbumSearchQuery implements QueryBuilderInterface
{
    private $query = [];
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
    ) {
    }

    public function query(): array
    {
        $this->queryKeyword();
        if ($this->type) {
            $this->query['bool']['must'][]['terms']['type'] = $this->type;
        }
        if ($this->classId) {
            $classIds = explode('_', $this->classId);
            foreach ($classIds as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }
        return $this->query;
    }
    public function queryKeyword()
    {
        if ($this->keyword) {
            $operator = $this->fuzzy ? 'or' : 'and';
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^1", "subtitle^1", "keyword^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }
        return $this;
    }

    public function getRedisKey()
    {
        $classId = $this->classId ? $this->classId : '0_0_0';
        $redisKey = sprintf(
            'ES_album03-20:%s:%s_%s_%s_%d_%d_%d',
            date('Y-m-d'),
            $this->keyword,
            $this->sortType,
            $classId,
            $this->type,
            $this->pageSize,
            $this->page
        );
        if ($this->fuzzy == 1) {
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
