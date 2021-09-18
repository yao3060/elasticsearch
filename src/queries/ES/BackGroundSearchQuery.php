<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\models\AssetUseTop;

class BackGroundSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    function __construct(
        public $keyword = 0,
        public int $page = 1,
        public int $pageSize = 40,
        public string $sceneId = '0',
        public int $isZb = 1,
        public string|int $sort = 'DESC',
        public int $useCount = 0,
        public string|int $kid = 0,
        public int $ratioId = 0,
        public int $class = 0,
        public int $isBg = 0
    ) {
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($this->ratioId > -1) {
            $newQuery['bool']['must'][]['match']['ratio'] = $this->ratioId;
        }
        if ($this->kid) {
            $newQuery['bool']['must'][]['terms']['kid_2'] = $this->kid;
        }
        if ($this->sceneId) {
            $newQuery['bool']['must'][]['terms']['scene_id'] = $this->sceneId;
        }
        if ($this->class) {
            $newQuery['bool']['must'][]['match']['class_id'] = $this->class;
        }

        if ($this->isBg) {
            $newQuery['bool']['must'][]['match']['kid_1'] = 2;
        }
        if ($this->useCount) {
            $useInfo = AssetUseTop::getLastInfo(2);
            switch ($this->useCount) {
                case 1:
                    $newQuery['bool']['filter'][]['range']['use_count']['gte'] = $useInfo['top1_count'];
                    break;
                case 2:
                    $newQuery['bool']['filter'][]['range']['use_count']['lt'] = $useInfo['top1_count'];
                    break;
            }
        }
        return $newQuery;
    }

    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        $sceneId = is_array($this->sceneId) ? $this->sceneId : [];
        $kid = is_array($this->kid) ? $this->kid : [];
        $keyword = $this->keyword ? $this->keyword : 0;
        $redisKey = sprintf(
            'ES_background2:%s:%s_%d_%s_%s_%d_%d_%d_%d_%d_%d_%d',
            date('Y-m-d'),
            $keyword,
            $this->page,
            implode('-', $kid),
            implode('-', $sceneId),
            $this->sceneId,
            $this->pageSize,
            $this->isZb,
            $this->class,
            $this->sort,
            $this->useCount,
            $this->isBg
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

    public static function sortByTime()
    {
        return 'created desc';
    }

    public static function sortDefault()
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
}
