<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\components\IpsAuthority;

class GifAssetSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public $keyword = 0,
        public  $page = 1,
        public  $pageSize = 40,
        public  $classId = '0',
        public  $isZb = 0,
        public  $prep = 0,
        public  $limitSize = 0,
    )
    {
    }

    public function query(): array
    {
        $newQuery = array();
        if (IpsAuthority::check(DESIGNER_USER) || IpsAuthority::check(AVATAR_USER)) {
            $isZb = 1;
        }else{
            $isZb =0;
        }
        $this->classId = is_array($this->classId) ? $this->classId : [];
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($this->classId) {
            $newQuery['bool']['must'][]['terms']['class_id'] = $this->classId;
        }
        if ($isZb) {
            $newQuery['bool']['filter'][]['range']['is_zb']['gte'] = $isZb;
        }
        if ($this->limitSize) {
            $newQuery['bool']['filter'][]['range']['size_w380']['lt'] = 1024;
        }
        return $newQuery;
    }
    public function queryKeyword($keyword, $is_or = false)
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

    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        //$redis_key = "ES_gif_asset: date('Y-m-d'):{$keyword}_{$page}_ ".implode('-', $class_id)." _{$pageSize}_{$is_zb}";
        $classId = is_array($this->classId) ? $this->classId : [];
        return sprintf(
            "ES_gif_asset: date('Y-m-d'):%s_%d_%s_%d_%d",
            $this->keyword,
            $this->page,
            implode('-', $classId),
            $this->pageSize,
            $this->isZb,
        );
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public function sortBy()
    {
        return 'create_date desc';
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
