<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SeoDetailKeywordForTitleQuery implements QueryBuilderInterface
{
    /**
     * @var string|int|mixed 关键字
     */
    public string $keyword;

    function __construct(
        $keyword = 0,
    )
    {
        $this->keyword = $keyword;
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->similarQueryKeyword($this->keyword);
        }
        $newQuery['bool']['filter'][]['range']['use']['lt'] = 5;
        return $newQuery;
    }

    public static function similarQueryKeyword($keyword, $type = 1)
    {
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["_keyword^1", "keyword^1"],
            'type' => 'most_fields',
            "operator" => "or"
        ];
        return $query;
    }

    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        // $redis_key = "ES_seo_detail_keyword_for_title:" . date('Y-m-d') . ":{$keyword}";
        return sprintf(
            'ES_seo_detail_keyword_for_title:%s:%s',
            date('Y-m-d'),
            $this->keyword,
        );
    }
}
