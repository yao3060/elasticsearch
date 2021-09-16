<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SeoSearchWordAssetQuery implements QueryBuilderInterface
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
        $type = 1,
    )
    {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->type = $type;
    }

    public function query(): array
    {
        $newQuery = $this->similarQueryKeyword($this->keyword, $this->type);
        return $newQuery;
    }
    public static function similarQueryKeyword($keyword, $type = 1)
    {
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["_keyword^1","keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        $query['bool']['must'][]['constant_score']['filter']['term']['type'] = $type;
        return $query;
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        // $redis_key = "ES_seo_similar_word_asset:v4:{$type}:{$keyword}";
        return sprintf(
            'ES_seo_similar_word_asset:v4:%d_%s',
            $this->type,
            $this->keyword,
        );
    }
}
