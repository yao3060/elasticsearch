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
class SearchWord extends BaseModel
{
    /**
     * @var int redis
     */
    const REDIS_DB = 5;

    public static function index()
    {
        return 'search_keyword';
    }

    public static function getDb()
    {
        return \Yii::$app->get('elasticsearch_search_keyword');
    }

    public static function type()
    {
        return 'list';
    }

    public function attributes()
    {
        return [
            'id',
            'title',
            'description',
            'created',
            'kid_1',
            'kid_2',
            'kid_3',
            'pr',
            'man_pr',
            'man_pr_add',
            'width',
            'height',
            'ratio',
            'scene_id',
            'is_zb'
        ];
    }

    /**
     * @param \app\queries\ES\SearchWordSearchQuery $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis(self::REDIS_DB, $query->getRedisKey());
        $log = 'SearchWord:redisKey:' . $query->getRedisKey();
        yii::info($log, __METHOD__);
        if ($return && isset($return['hit']) && $return['hit']) {
            Yii::info('bypass redis, redis key:' . $query->getRedisKey(), __METHOD__);
            return $return;
        }
        try {
            $info = self::find()
                ->source(['word_id', 'keyword', 'results', 'count', 'pinyin'])
                ->query($query->query())
                ->orderBy($query->sortDefault())
                ->limit($query->pageSizeSet())
                ->createCommand()
                ->search([], ['track_scores' => true])['hits'];
            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $this_id = (int)$value['_source']['word_id'];
                $return['ids'][] = $this_id;
                $return['results'][$this_id] = $value['_source']['results'];
                $return['count'][$this_id] = $value['_source']['count'];
                $return['keyword'][$this_id] = $value['_source']['keyword'];
                $return['pinyin'][$this_id] = $value['_source']['pinyin'];
                $return['score'][$this_id] = $value['sort'][0];
            }
            Tools::setRedis(self::REDIS_DB, $query->getRedisKey(), $return, 126000);
            return $return;
        } catch (Exception $e) {
            $info['hit'] = 0;
            $info['ids'] = [];
            $info['score'] = [];
            \Yii::error($e->getMessage(), __METHOD__);
            return $info;
        }
    }
}
