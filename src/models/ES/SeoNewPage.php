<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

class SeoNewPage extends BaseModel
{
    public static $redisDb = 8;

    public static function index()
    {
        return 'seo_new_page';
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return ['id', 'keyword', '_keyword'];
    }

    /**
     * @param  \app\queries\ES\SeoNewPageSeoSearchQuery  $query
     * @return array|false|mixed
     */
    public function seoSearch(QueryBuilderInterface $query)
    {
        $redisKey = $query->getRedisKey();

        $return = Tools::getRedis(self::$redisDb, $redisKey);

        if (!empty($return) && isset($return['hit']) && $return['hit'] && Tools::isReturnSource() === false) {
            \Yii::info("seo new page data source from redis", __METHOD__);
            return $return;
        }

        $responseData = [
            'hit' => 0,
            'ids' => [],
            'score' => []
        ];

        try {
            $info = self::find()
                ->source(['id', '_keyword'])
                ->query($query->query())
                ->limit($query->pageSize)
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            $total = $info['total'] ?? 0;

            if (isset($info['hits']) && $info['hits'] && $total > 0) {
                foreach ($info['hits'] as $k => $v) {
                    $responseData[$k]['id'] = $v['_source']['id'] ?? 0;
                    $responseData[$k]['keyword'] = $v['_source']['_keyword'] ?? '';
                }
            }
        } catch (\Throwable $e) {
            \Yii::error("SeoNewPage Model Error: " . $e->getMessage(), __METHOD__);
        }

        Tools::setRedis(self::$redisDb, $redisKey, $responseData, 86400 * 30);

        return $responseData;
    }
}
