<?php

namespace app\models\ES;

use app\interfaces\ES\QueryBuilderInterface;

class Picture extends BaseModel
{
    public function search(QueryBuilderInterface $query): array
    {
        // put your search logic here:
        return [
            'data' => [], // self::find()->query($query)->all(),
            'query' => $query->query(),
            'sort' => $query->sort,
            'page' => $query->page,
            'page_size' => $query->pageSize,
        ];
    }
}
