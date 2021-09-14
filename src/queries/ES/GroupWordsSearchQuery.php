<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use app\services\designers\DesignerRecommendAssetTagService;

class GroupWordsSearchQuery implements QueryBuilderInterface
{
    /**
     * @var string|int|mixed 关键字
     */
    public string $keyword;
    /**
     * @var int|mixed 页码
     */
    public int $page;
    /**
     * @var int|mixed 每页数量
     */
    public int $pageSize;
    public string $search;
    public string $searchAll;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $search = 0,
        $searchAll = 0,
    )
    {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->search = $search;
        $this->searchAll = $searchAll;
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
        return $newQuery;
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

    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
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
    public function pageSizeSet(){
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
