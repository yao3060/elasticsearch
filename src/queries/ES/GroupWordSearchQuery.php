<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\services\designers\DesignerRecommendAssetTagService;

class GroupWordSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public  $keyword = 0,
        public  $page = 1,
        public  $pageSize = 40,
        public  $search = '0',
        public  $searchAll = '0',
    ) {
    }

    public function query(): array
    {
        if ($this->searchAll) {
            $keyword = DesignerRecommendAssetTagService::getRecommendAssetKws(5);
            $shouldMatch = [];
            foreach ($keyword as $keywordVal) {
                $shouldMatch[] = [
                    'match' => [
                        'keyword' => $keywordVal
                    ]
                ];
            }
            $newQuery['bool']['should'][] = $shouldMatch;
        } elseif ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword, false, true);
        }
        if (!empty($this->search)) {
            $newQuery['bool']['must'][]['multi_match'] = [
                'query' => $this->search,
                'fields' => ["keyword^1"],
                'type' => 'most_fields',
                "operator" => 'and'
            ];
        }
        // FIXME: @yangshangpu 这儿会出现错误 ·Undefined variable $newQuery·, 例如： /v1/groups?keyword=
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
        $redisKey = "ES_group_word:" . date('Y-m-d') .
            ":{$this->keyword}_{$this->page}_" .
            "_{$this->pageSize}";
        if (!empty($this->search)) {
            $redisKey .= '_' . $this->search;
        }
        if (!empty($this->searchAll)) {
            $redisKey .= '_' . $this->searchAll . '_v1';
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

    public function sortBy()
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
