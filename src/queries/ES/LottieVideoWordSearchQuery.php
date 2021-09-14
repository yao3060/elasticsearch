<?php


namespace app\queries\ES;


class LottieVideoWordSearchQuery extends BaseTemplateSearchQuery
{
    public function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $prep = 0,
        public $sort = 'create_date desc',
        public $offset = 0,
        public $fuzzy = false
    )
    {

    }

    public function queryKeyword()
    {
        if (!empty($this->keyword)) {
            $operator = isset($this->fuzzy) && $this->fuzzy ? 'or' : 'and';
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^5", "description^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }

        return $this;
    }

    public function query(): array
    {
        $this->queryKeyword();

        return $this->query;
    }

    public function getRedisKey()
    {
        $this->keyword = $this->keyword ?: 0;

        $this->offset = (intval($this->page) - 1) * intval($this->pageSize);

        $redisKey = "ES_video:lottie_word:" . date('Y-m-d') . ":{$this->keyword}_{$this->page}_ " . " _{$this->pageSize}";

        return $redisKey;
    }
}
