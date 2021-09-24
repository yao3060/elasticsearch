<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\services\designers\DesignerRecommendAssetTagService;

class TemplateSinglePageSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public int $c1 = 0,
        public int $page = 1,
        public int $pageSize = 50,
        public array|string $c2 = [],
        public array|string $c3 = [],
    )
    {
    }

    public function query(): array
    {
        ksort($this->c2);
        ksort($this->c3);
        $newQuery = [];
        $newQuery['bool']['must'][]['terms']['c_id'] = [$this->c1];
        if ($this->c2) {
            foreach ($this->c2 as $class_id) {
                if (intval($class_id) > 0) {
                    $query['bool']['must'][]['terms']['c_id'] = [$class_id];
                }
            }
        }
        if ($this->c3) {
            foreach ($this->c3 as $class_id) {
                if (intval($class_id) > 0) {
                    $query['bool']['must'][]['terms']['c_id'] = [$class_id];
                }
            }
        }
        return $newQuery;
    }
    public function getRedisKey()
    {
        //$redis_key = 'template-sp:search:' . $c1 . ':' . implode('-', $c2) . ':' . implode('-', $c3) . "_" . $page;
        return sprintf(
            "template-sp:search:%s:%s:%s_%d",
            $this->c1,
            implode('-', $this->c2),
            implode('-', $this->c3),
            $this->page
        );
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
}
