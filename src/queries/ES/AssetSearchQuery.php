<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\models\AssetUseTop;

class AssetSearchQuery implements QueryBuilderInterface
{
    //搜索所需 要参数
    function __construct(
        public $keyword = 0,
        public int $page = 1,
        public int $pageSize = 40,
        public  $sceneId = 0,
        public  $isZb = 1,
        public  $sort = 'DESC',
        public  $useCount = 0
    )
    {
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($this->sceneId) {
            $newQuery['bool']['must'][]['terms']['scene_id'] = $this->sceneId;
        }
        $newQuery['bool']['filter'][]['term']['kid_1'] = 1;
        if ($this->useCount) {
            $useInfo = AssetUseTop::getLastInfo(1);
            switch ($this->useCount) {
                case 1:
                    $newQuery['bool']['filter'][]['range']['use_count']['gte'] = $useInfo['top1_count'];
                    break;
                case 2:
                    $newQuery['bool']['filter'][]['range']['use_count']['lt'] = $useInfo['top1_count'];
                    break;
            }
        } else {
            $useInfo = '';
        }
        return  $newQuery;
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
        if ($this->sort === 'bytime') {
            $sortBy = $this->sortByTime();
        } else {
            $sortBy = $this->sortDefault();
        }
        return $sortBy;
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
    protected function sortByTime()
    {
        return 'created desc';
    }

    protected function sortDefault()
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
    protected function queryKeyword($keyword, $is_or = false)
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
        return sprintf(
            'ES_asset2:%s:%s_%d_%s_%d_%d_%d_%d',
            date('Y-m-d'),
            $this->keyword,
            $this->page,
            implode('-', $sceneId),
            $this->pageSize,
            $this->isZb,
            $this->sort,
            $this->useCount
        );
    }
}
