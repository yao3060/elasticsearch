<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class PptTemplate extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = '_search';
    const REDIS_EXPIRE = 86400;

    public static function index()
    {
        return 'template_single_page_index';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return [
            'id',
            'template_id',
            'page',
            'c_id'
        ];
    }

    /**
     * @param \app\queries\ES\PptTemplateSearchQuery $query
     * @return array 2021-09-23
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'GroupWord:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return && isset($return['hit']) && $return['hit'] && !Tools::isReturnSource()) {
            Yii::info('bypass by redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        $info = [
            'hit' => 0,
            'ids' => [],
            'score' => [],
            'total' => 0,
        ];
        try {
            $info = self::find()
                ->source(['temple_id'])
                ->query($query->query())
                ->offset($query->queryOffset())
                ->limit($query->pageSizeSet())
                ->orderBy(['id' => SORT_DESC])
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }
        $data = [
            'total' => $info['total'],
            'hit' => $info['total'] ?? 0 > 10000 ? 10000 : $info['total'],
        ];
        foreach ($info['hits'] as $value) {
            $data['ids'][] = $value['_id'];
            $data['score'][$value['_id']] = $value['sort'][0];
        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $data, self::REDIS_EXPIRE);
        return $return;
    }
}
