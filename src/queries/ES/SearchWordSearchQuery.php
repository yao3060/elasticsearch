<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SearchWordSearchQuery implements QueryBuilderInterface
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
    public int $type;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $type = 1
    )
    {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->type = $type;
    }

    public function query(): array
    {
        $newQuery = $this->queryKeyword($this->keyword);
        $newQuery['bool']['must'][]['match']['type'] = $this->type;
        $newQuery['bool']['filter'][]['range']['results']['gte'] = 1;
        return $newQuery;
    }
    public static function queryKeyword($keyword)
    {
        if (mb_strlen($keyword) > 1) {
            $query['bool']['must'][]['match']['keyword'] = [
                'query' => $keyword,
                "operator" => "and"
            ];
        } else {
            $query['bool']['must'][]['prefix']['keyword'] = [
                'value' => $keyword
            ];
        }
        return $query;
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public static function sortDefault()
    {
        $source = "doc['count'].value*500+doc['results'].value*1";
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
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        $redisKey = sprintf(
            'searchword200909:%s_%d_%d',
            $this->keyword,
            $this->type,
            $this->pageSize
        );
        return $redisKey;
    }
}
