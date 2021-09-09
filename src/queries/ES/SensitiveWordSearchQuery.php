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

    public function query(): array
    {
        return SensitiveWord::queryWord($this->keyword);
    }

    public function getRedisKey()
    {
        $redis_key = "is_ban_word:" . $this->keyword;

        return $redis_key;
    }
}
