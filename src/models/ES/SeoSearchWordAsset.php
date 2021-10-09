<?php

namespace app\models\ES;

use app\components\Tools;
use yii\base\Exception;
use app\interfaces\ES\QueryBuilderInterface;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class SeoSearchWordAsset extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 8;

    public static function index()
    {
        return 'seo_search_word_asset';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'keyword', '_keyword', 'pinyin', 'type', 'weight'];
    }

    /**
     * @param \app\queries\ES\SeoSearchWordAssetQuery $query
     * @return array 2021-09-16
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function seoSearch(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'SeoSearchWordAsset:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        try {
            $info = self::find()
                ->source(['id', '_keyword', 'pinyin', 'weight'])
                ->query($query->query())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            $total = $info['total'] ?? 0;
            $data = [];
            if ($total > 0 && isset($info['hits']) && $info['hits']) {
                foreach ($info['hits'] as $v) {
                    $data[] = [
                        'id' => $v['_source']['id'] ?? 0,
                        'keyword' => $v['_source']['_keyword'] ?? '',
                        'pinyin' => $v['_source']['pinyin'] ?? '',
                        'weight' => $v['_source']['weight'] ?? 0
                    ];
                }
            }
            $response = [
                'list' => $data,
                'total' => $total
            ];
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $response, 86400 * 30);
            return $response;
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            $response = [
                'list' => '',
                'total' => ''
            ];
            return $response;
        }
    }
}
