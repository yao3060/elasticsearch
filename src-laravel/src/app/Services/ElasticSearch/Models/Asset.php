<?php

namespace App\Services\ElasticSearch\Models;

class Asset extends Model
{
    public function stats()
    {
        return [
            'node' => $this->connection->nodes()->stats(),
            'cluster' => $this->connection->cluster()->stats(),
        ];
    }
    public function search(
        $keyword = 0,
        int $page = 1,
        $scene_id = [],
        int $pagesize = 40,
        $is_zb = 0,
        $sort = 0,
        $use_count = 0
    ) {
        return ['asset'];
    }
}
