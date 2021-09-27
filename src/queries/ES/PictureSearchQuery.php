<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class PictureSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $sceneId = [],
        public $isZb = 1,
        public $kid = [],
        public $vipPic = 0,
        public $ratioId = 0
    )
    {
    }

    public function query(): array
    {
        $sceneId = is_array($this->sceneId) ? $this->sceneId : [];
        $kid = is_array($this->kid) ? $this->kid : [];
        $ratioId = isset($this->ratioId) ? $this->ratioId : '-1';
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($ratioId > -1) {
            $newQuery['bool']['must'][]['match']['ratio'] = $ratioId;
        }
        if ($kid) {
            $newQuery['bool']['must'][]['terms']['kid_2'] = $kid;
        }
        if ($sceneId) {
            $newQuery['bool']['must'][]['terms']['scene_id'] = $sceneId;
        }
        if ($this->isZb) {
            $newQuery['bool']['filter'][]['range']['is_zb']['gte'] = $this->isZb;
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
        $sceneId = is_array($this->sceneId) ? $this->sceneId : [];
        $kid = is_array($this->kid) ? $this->kid : [];
        $ratioId = isset($this->ratioId) ? $this->ratioId : '-1';
        $redisKey = sprintf(
            'ES_picture2:%s:%s_%d_%s_%s_%d_%d_%d_%d_v1',
            date('Y-m-d'),
            $this->keyword,
            $this->page,
            implode('-', $kid),
            implode('-', $sceneId),
            $ratioId,
            $this->pageSize,
            $this->isZb,
            $this->vipPic
        );
        return $redisKey;
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
        $source = "doc['pr'].value+(int)(_score*10)";
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
