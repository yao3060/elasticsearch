<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class SearchWord extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 5;

    public static function index() {
        return 'search_keyword';
    }
    public static function getDb()
    {
        return \Yii::$app->get('elasticsearch_search_keyword');
    }
    public static function type() {
        return 'list';
    }
    public static function sortByHot() {
        return 'sort desc';
    }
    public function attributes() {
        return ['id', 'title', 'description', 'created', 'kid_1', 'kid_2', 'kid_3', 'pr', 'man_pr', 'man_pr_add', 'width', 'height', 'ratio', 'scene_id','is_zb'];
    }
    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = sprintf('searchword200909:%s_%d_%d',$query->keyword,$query->type,$query->pageSize);
        $return = Tools::getRedis($this->redisDb, $redisKey);
        if(!$return){
            $newQuery = $this->queryKeyword($query->keyword);
            $newQuery['bool']['must'][]['match']['type'] = $query->type;
            $newQuery['bool']['filter'][]['range']['results']['gte'] = 1;
            $sort = $this->sortDefault();
            try {
                $info = self::find()
                    ->source(['word_id', 'keyword', 'results', 'count', 'pinyin'])
                    ->query($newQuery)
                    ->orderBy($sort)
                    ->limit($query->pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\exception $e) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
            }
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

            Tools::setRedis($this->redisDb, $redisKey, $return, 126000);
        }
        return $return;
    }
    public static function queryKeyword($keyword) {
        if (mb_strlen($keyword) > 1) {
            $query['bool']['must'][]['match']['keyword'] = [
                'query' => $keyword,
                "operator" => "and"
            ];
        } else {
            $query['bool']['must'][]['prefix']['keyword'] = [
                'value' => $keyword
            ];
        }
        return $query;
    }
    public static function sortDefault() {
        $source = "doc['count'].value*500+doc['results'].value*1";
        $sort['_script'] = [
            'type' => 'number',
            'script' => [
                "lang" => "painless",
                "source" => $source
            ],
            'order' => 'desc'
        ];
        return $sort;
    }


}
