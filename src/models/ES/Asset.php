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
        $redis_key = "ES_asset2:" . date('Y-m-d') . ":{$keyword}_{$page}_" . implode('-', $scene_id) . "_{$pagesize}_{$is_zb}_{$sort}_{$use_count}";
        $return = Tools::getRedis(self::$redis_db, $redis_key);
        return [
            'data' => [], // self::find()->query($query)->all(),
            'query' => $query->query(),
            'sort' => $query->sort,
            'page' => $query->page,
            'page_size' => $query->pageSize,
        ];
    }
}
