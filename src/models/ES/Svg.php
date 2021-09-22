<?php

namespace app\models\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

class Svg extends BaseModel
{
    const REDIS_DB = 8;

    /**
     * @param \app\queries\ES\SvgSearchQuery $query
     * @return array
     * @throws Exception
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = $query->getRedisKey();

        $return = Tools::getRedis(self::REDIS_DB, $redisKey);

        if (!$return || Tools::isReturnSource() || IpsAuthority::check(DESIGNER_USER)) {

            $return['hit'] = 0;
            $return['ids'] = [];
            $return['score'] = [];

            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($query->query())
                    ->orderBy($query->sort)
                    ->offset(($query->page - 1) * $query->pageSize)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];

                if (isset($info['hits']) && sizeof($info['hits'])) {
                    $total = $info['total'] ?? 0;
                    $return['hit'] = $total > 10000 ? 10000 : $total;
                    foreach ($info['hits'] as $value) {
                        $return['ids'][] = $value['_id'];
                        $return['score'][$value['_id']] = $value['sort'][0];
                    }
                }
            } catch (\Exception $e) {
                throw new Exception($e->getMessage());
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
