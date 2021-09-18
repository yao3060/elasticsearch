<?php


namespace app\models\ES;


use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use yii\base\Exception;

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

    public function seoSearch(QueryBuilderInterface $query)
    {
        $return = Tools::getRedis(self::$redisDb, $query->getRedisKey());

        if (!$return || Tools::isReturnSource()) {

            $return['hit'] = 0;
            $return['ids'] = [];
            $return['score'] = [];

            try {

                $info = self::find()
                    ->source(['id', '_keyword'])
                    ->query($query->query())
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];

            } catch (\exception $e) {

                throw new Exception($e->getMessage());

            }

            $total = $info['total'] ?? [];

            if (isset($info['hits']) && $info['hits'] && $total > 0) {

                foreach ($info['hits'] as $k=>$v) {
                    $return[$k]['id'] = $v['_source']['id'];
                    $return[$k]['keyword'] = $v['_source']['_keyword'];
                }
            }

//            Tools::setRedis(self::$redisDb, $query->getRedisKey(), $return, 86400 * 30);

        }

        return $return;
    }
}
