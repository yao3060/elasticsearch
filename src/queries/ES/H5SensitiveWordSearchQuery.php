<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class H5SensitiveWordSearchQuery implements QueryBuilderInterface
{
    private $query = [];

    function __construct(
        public $word = 0,
    )
    {
    }
    public function query(): array
    {
        $this->queryWord();
        return $this->query;
    }
    public function queryWord()
    {
        $this->query['bool']['must'][]['match']['word'] = [
            'query' => $this->word,
            "operator" => "or",
        ];
        return $this;
    }

    public function getRedisKey()
    {
    }

}
