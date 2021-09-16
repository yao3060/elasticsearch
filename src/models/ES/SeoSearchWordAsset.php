<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class SeoSearchWordAsset extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

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
        $return = Tools::getRedis($this->redisDb, $query->getRedisKey());
        if (!$return) {
            try {
                $info = self::find()
                    ->source(['id', '_keyword', 'pinyin', 'weight'])
                    ->query($query->query())
                    ->limit($query->pageSizeSet())
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\exception $e) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
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
            Tools::setRedis($this->redisDb, $query->getRedisKey(), $return, 86400 * 30);
        }
        return $return;
    }

}
