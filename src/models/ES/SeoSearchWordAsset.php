<?php

namespace app\models\ES;

use app\components\Tools;
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
        $log = 'SeoSearchWordAsset:redisKey:'.$query->getRedisKey();
        yii::info($log,__METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            return $return;
        }
        $return['hit'] = 0;
        $return['ids'] = [];
        $return['score'] = [];
        try {
            $info = self::find()
                ->source(['id', '_keyword', 'pinyin', 'weight'])
                ->query($query->query())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }
        if ($info['total'] > 0) {
            foreach ($info['hits'] as $k => $v) {
                $return[$k]['id'] = $v['_source']['id'];
                $return[$k]['keyword'] = $v['_source']['_keyword'];
                $return[$k]['pinyin'] = $v['_source']['pinyin'];
                if (isset($v['_source']['weight'])){
                    $return[$k]['weight'] = $v['_source']['weight'];
                }else{
                    $return[$k]['weight'] = 0;
                }

            }

        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 86400 * 30);
        return $return;
    }

}
