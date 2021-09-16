<?php


namespace app\queries\ES;

use app\models\ES\SensitiveWord;

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

        return $this->query();
    }

    public function getRedisKey()
    {
        $redis_key = "is_ban_word:" . $this->keyword;

        return $redis_key;
    }
}
