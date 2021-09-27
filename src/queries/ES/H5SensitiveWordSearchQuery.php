<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class H5SensitiveWordSearchQuery implements QueryBuilderInterface
{
    function __construct(
        public $word = 0,
    )
    {
    }
    public function query(): array
    {
        $newQuery = $this->queryWord($this->word);
        return $newQuery;
    }
    public function queryWord($word)
    {
        $query['bool']['must'][]['match']['word'] = [
            'query' => $word,
            "operator" => "or",
        ];
        return $query;
    }

    public function getRedisKey()
    {
    }

}
