<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

class Svg extends BaseModel
{
    const REDIS_DB = 8;

    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = sprintf(
            'ES_svg2:%s:%s_%d_%s_%d',
            date('Y-m-d'),
            $query->keyword,
            $query->page,
            implode('-', $query->kid2),
            $query->pageSize
        );

        $return = Tools::getRedis(self::REDIS_DB, $redisKey);

        // !$return || Tools::isReturnSource() || IpsAuthority::check(DESIGNER_USER)
        if (!$return) {
            $return = [];
            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($query->query())
                    ->orderBy($query->sort)
                    ->offset(($query->page - 1) * $query->pageSize)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];

                $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
                foreach ($info['hits'] as $value) {
                    $return['ids'][] = $value['_id'];
                    $return['score'][$value['_id']] = $value['sort'][0];
                }
            } catch (\Exception $e) {
                $return['hit'] = 0;
                $return['ids'] = [];
                $return['score'] = [];
            }
        }
        return $return;
    }

    public static function index()
    {
        return 'svg';
    }

    public static function type()
    {
        return 'list';
    }
}
