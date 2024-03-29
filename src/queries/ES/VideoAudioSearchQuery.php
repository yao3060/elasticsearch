<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class VideoAudioSearchQuery implements QueryBuilderInterface
{
    private $query = [];
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $parentsId = 0,
        public $classId = '0',
        public $prep = 0,
        public $isDesigner = 0,
        public $isVip = 0
    ) {
        $this->page = (int) $this->page;
        $this->pageSize = (int) $this->pageSize;
    }

    public function query(): array
    {
        $this->queryKeyword();
        $class_id = $this->classId ? $this->classId : [];
        if (!is_array($class_id)) {
            $class_id = [$class_id];
        }
        if ($class_id) {
            foreach ($class_id as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }
        $this->query['bool']['must'][]['match']['parents_id'] = $this->parentsId;
        if ($this->isDesigner == 1) {
            $this->query['bool']['must'][]['term']['is_vip'] = 0;
        }
        if ($this->isVip == 1) {
            $this->query['bool']['must'][]['term']['is_vip'] = 1;
        }
        return $this->query;
    }

    public function queryKeyword($is_or = false)
    {
        if ($this->keyword) {
            $operator = $is_or ? 'or' : 'and';
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }

        return $this;
    }

    public function getRedisKey()
    {
        $class_id = $this->classId ? $this->classId : [];
        if (!is_array($class_id)) {
            $class_id = [$class_id];
        }
        $redisKey = sprintf(
            'ES_video:audio:%s:%s_%s_%d_%s_%d',
            date('Y-m-d'),
            $this->parentsId,
            $this->keyword,
            $this->page,
            ' '.implode('-', $class_id).' ',
            $this->pageSize
        );
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

    public function sortBy()
    {
        if ($this->isVip == 1) {
            $sort = $this->sortByOrderTime();
        } else {
            $sort = $this->sortByTime();
        }
        if ($this->isDesigner == 0 && $this->isVip == 0) {  // 用户视频编辑器原版音乐排版使用pr排序
            $sort = $this->sortByPr();
        }
        return $sort;
    }

    public function sortByOrderTime()
    {
        return 'create_date asc';
    }

    public function sortByTime()
    {
        return 'create_date desc';
    }

    public function sortByPr()
    {
        return 'pr desc';
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
