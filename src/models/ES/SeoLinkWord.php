<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;
use Yii;

/**
 * @package app\models\ES
 * author  ysp
 */
class SeoLinkWord extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 8;
    const REDIS_EXPIRE = 86400;

    public static function index()
    {
        return 'seo_link_word';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return [
            'id',
            'keyword',
            '_keyword',
            'pinyin'
        ];
    }

    /**
     * @param \app\queries\ES\SeoLinkWordSearchQuery $query
     * @return array 2021-09-23
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'SeoLinkWordSearch:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            return $return;
        }
        $return['hit'] = 0;
        $return['ids'] = [];
        $return['score'] = [];
        try {
            $info = self::find()
                ->source(['id', 'keyword'])
                ->query($query->query())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (\exception $e) {
            $info['total'] = 0;
            $return['is_seo_search_keyword'] = false;
        }
        if ($info['total'] > 0) {
            $return['is_seo_search_keyword'] = true;
            $return['id'] = $info['hits'][0]['_id'];
            $return['keyword'] = $query->keyword;
        }
        Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, self::REDIS_EXPIRE);
        return $return;
    }

    /**
     * Seo搜索
     *
     * @param \app\queries\ES\SeoLinkWordSearchQuery $query
     * @return array
     */
    public function seoSearch(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getSeoRedisKey());
        $log = 'SeoLinkWordSeoSearch:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            return $return;
        }
        $info['hit'] = 0;
        $info['ids'] = [];
        $info['score'] = [];
        $info['total'] = 0;
        try {
            $info = self::find()
                ->source(['id', '_keyword', 'pinyin'])
                ->orderBy($query->sort())
                ->query($query->seoQuery())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
        } catch (Exception $e) {
            \Yii::error($e->getMessage(), __METHOD__);
            throw new Exception($e->getMessage());
        }
        if ($info['total'] > 0) {
            foreach ($info['hits'] as $k => $v) {
                $return[$k]['id'] = $v['_source']['id'];
                $return[$k]['keyword'] = $v['_source']['_keyword'];
                $return[$k]['pinyin'] = $v['_source']['pinyin'];
            }
        } else {
            $return[0]['id'] = 0;
        }
        Tools::setRedis(self::REDIS_DB, $query->getSeoRedisKey(), $return, 86400 * 30);
        return $return;
    }
}
