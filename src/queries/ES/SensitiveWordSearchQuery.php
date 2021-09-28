<?php


namespace app\queries\ES;


class SensitiveWordSearchQuery extends BaseTemplateSearchQuery
{

    public function __construct(
        public $keyword = ''
    )
    {
    }

    public function queryWord()
    {
        $this->query['bool']['must'][]['match']['word'] = [
            'query' => $this->keyword,
            "operator" => "or"
        ];

        return $this;
    }

    public function query(): array
    {
        $this->queryWord();

        return $this->query;
    }

    public function getRedisKey()
    {
        return "is_ban_word_v2:" . $this->keyword;
    }
}
