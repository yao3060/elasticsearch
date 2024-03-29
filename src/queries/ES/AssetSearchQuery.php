<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use \app\models\Backend\AssetUseTop;

class AssetSearchQuery implements QueryBuilderInterface
{
    private $query = [];
    //搜索所需 要参数
    function __construct(
        public $keyword = 0,
        public  $page = 1,
        public  $pageSize = 40,
        public  $sceneId = 0,
        public  $isZb = 1,
        public  $sort = 'DESC',
        public  $useCount = 0
    ) {
    }

    public function query(): array
    {
        $this->queryKeyword();
        if ($this->sceneId) {
            $this->query['bool']['must'][]['terms']['scene_id'] = $this->sceneId;
        }
        $this->query['bool']['filter'][]['term']['kid_1'] = 1;
        if ($this->useCount) {
            $useInfo = AssetUseTop::getLatestBy('kid_1', 1);
            switch ($this->useCount) {
                case 1:
                    $this->query['bool']['filter'][]['range']['use_count']['gte'] = $useInfo['top1_count'];
                    break;
                case 2:
                    $this->query['bool']['filter'][]['range']['use_count']['lt'] = $useInfo['top1_count'];
                    break;
            }
        }
        return  $this->query;
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
    public function sortByTime()
    {
        return 'created desc';
    }

    public function sortDefault()
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
    public function queryKeyword($is_or = false)
    {

        if ($this->keyword){
            $operator = $is_or ? 'or' : 'and';
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^5", "description^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }
        return $this;
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
