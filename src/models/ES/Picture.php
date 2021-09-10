<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class Picture extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

    public static function index()
    {
        return 'picture2';
    }

    public static function type()
    {
        return 'list';
    }

    public static function sortByTime()
    {
        return 'created desc';
    }

    public static function sortByHot()
    {
        return 'edit desc';
    }

    public function attributes()
    {
        return ['id', 'title', 'description', 'created', 'kid_1', 'kid_2', 'kid_3', 'pr', 'man_pr', 'man_pr_add', 'width', 'height', 'ratio', 'scene_id', 'is_vip_asset'];
    }

    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分 数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $sceneId = is_array($query->sceneId) ? $query->sceneId : [];
        $kid = is_array($query->kid) ? $query->kid : [];
        $ratioId = isset($query->ratioId) ? $query->ratioId : '-1';
        $redisKey = sprintf('ES_picture2:%s:%s_%d_%s_%s_%d_%d_%d_%d_v1',
            date('Y-m-d'), $query->keyword, $query->page, implode('-', $kid), implode('-', $sceneId),
            $ratioId, $query->pageSize, $query->isZb, $query->vipPic);
        $return = Tools::getRedis($this->redisDb, $redisKey);
        $pageSize = $query->pageSize;
        if (!$return || !$return['hit']) {
            unset($return);
            if ($query->keyword) {
                $newQuery = $this->queryKeyword($query->keyword);
            }
            if ($ratioId > -1) {
                $newQuery['bool']['must'][]['match']['ratio'] = $ratioId;
            }
            if ($kid) {
                $newQuery['bool']['must'][]['terms']['kid_2'] = $kid;
            }
            if ($sceneId) {
                $newQuery['bool']['must'][]['terms']['scene_id'] = $sceneId;
            }
            if ($query->isZb) {
                $newQuery['bool']['filter'][]['range']['is_zb']['gte'] = $query->isZb;
            }
            $sort = $this->sortDefault();
            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($newQuery)
                    ->orderBy($sort)
                    ->offset(($query->page - 1) * $pageSize)
                    ->limit($pageSize)
                    ->createCommand()
                    ->search([], ['track_scores' => true])['hits'];
            } catch (\exception $e) {
                $info['hit'] = 0;
                $info['ids'] = [];
                $info['score'] = [];
            }
            $return['hit'] = $info['total'] > 10000 ? 10000 : $info['total'];
            foreach ($info['hits'] as $value) {
                $return['ids'][] = $value['_id'];
                //$return['is_vip_asset'][$value['_id']] = $value['is_vip_asset'];
                $return['is_vip_asset'][$value['_id']] = 0;
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            Tools::setRedis($this->redisDb, $redisKey, $return, 86400);
        }
        return $return;
    }

    public static function queryKeyword($keyword, $is_or = false)
    {
        $operator = $is_or ? 'or' : 'and';
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["title^5", "description^1"],
            'type' => 'most_fields',
            "operator" => $operator
        ];
        return $query;
    }

    public static function sortDefault()
    {
        //        $source = "doc['pr'].value-doc['man_pr'].value+doc['man_pr_add'].value";
        $source = "doc['pr'].value+(int)(_score*10)";
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
