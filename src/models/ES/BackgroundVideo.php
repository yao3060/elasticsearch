<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

class BackgroundVideo extends BaseModel
{
    public static $redis_db = 8;

    public static function validateRules()
    {
        return [
        ];
    }

    /**
     * 搜索背景视频
     * @param QueryBuilderInterface $query
     * @return array
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::$redis_db, $query->getRedisKey());

        if (!$return || Tools::isReturnSource()) {
            unset($return);

            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($query->query())
                    ->orderBy($query->sort)
                    ->offset(($query->page - 1) * $query->pageSize)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\Throwable $throwable) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
            }

            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                $return['score'][$value['_id']] = $value['sort'][0];
            }
//            Tools::setRedis(self::$redis_db, $query->getRedisKey(), $return, 86400);
        }

        return $return;
    }
}
