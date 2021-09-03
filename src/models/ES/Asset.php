<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

class Asset extends BaseModel
{
    public static $redis_db = 8;
    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-03
     */
    public function search(QueryBuilderInterface $query): array
    {
        $is_zb = 1;
        $sceneId = $query->sceneId ? $query->sceneId : [];
        if (!is_array($sceneId)) {
            $sceneId = [$sceneId];
        }
        $redis_key = "ES_asset2:" . date('Y-m-d') . ":{$query->key_word}_{$query->page}_" . implode('-', $sceneId) . "_{$query->pageSize}_{$query->isZb}_{$query->sort}_{$query->use_count}";
        $return = Tools::getRedis(self::$redis_db, $redis_key);
        var_dump($return);exit();
        return [
            'data' => [], // self::find()->query($query)->all(),
            'query' => $query->query(),
            'sort' => $query->sort,
            'page' => $query->page,
            'page_size' => $query->pageSize,
        ];
    }
}
