<?php


namespace app\queries\ES;


use app\components\Tools;
use app\models\ES\Template;

class SensitiveWordSearchQuery extends BaseTemplateSearchQuery
{
    public $keyword;

    public function __construct($params)
    {
        $this->keyword = $params['keyword'] ?? '';
    }

    public function query(): array
    {
        $redisKey = $this->getRedisKey();
        $isBanWord = Tools::getRedis(6, $redisKey);
        if (!$isBanWord) {
            $isBanWord = Template::queryKeyword($this->keyword);
        }

        return $isBanWord;
    }

    public function getRedisKey()
    {
        $redis_key = "is_ban_word:" . $this->keyword;

        return $redis_key;
    }
}
