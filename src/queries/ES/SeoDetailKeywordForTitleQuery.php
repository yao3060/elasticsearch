<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SeoDetailKeywordForTitleQuery implements QueryBuilderInterface
{
    private $query = [];
    /**
     * @var string|int|mixed 关键字
     */
    function __construct(
        public $keyword = 0,
    )
    {
    }

    public function query(): array
    {
        $this->similarQueryKeyword();
        $this->query['bool']['filter'][]['range']['use']['lt'] = 5;
        return $this->query;
    }

    public function similarQueryKeyword()
    {
        if ($this->keyword) {
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["_keyword^1", "keyword^1"],
                'type' => 'most_fields',
                "operator" => "or"
            ];
        }

        return $this;
    }

    public function getRedisKey()
    {
        // $redis_key = "ES_seo_detail_keyword_for_title:" . date('Y-m-d') . ":{$keyword}";
        return sprintf(
            'ES_seo_detail_keyword_for_title:%s:%s',
            date('Y-m-d'),
            $this->keyword,
        );
    }
}
