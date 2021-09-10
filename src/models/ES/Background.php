<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\AssetUseTop;

/**
 * @package app\models\ES
 * author  ysp
 */
class Background extends BaseModel
{
    private $redisDb = 8;

    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-06
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $sceneId = is_array($query->sceneId) ? $query->sceneId : [];
        $kid = is_array($query->kid) ? $query->kid : [];
        $redisKey = sprintf('ES_background2:%s:%s_%d_%s_%s_%d_%d_%d_%d_%d_%d_%d',
            date('Y-m-d'), $query->keyword, $query->page, implode('-', $kid), implode('-', $sceneId),
            $query->sceneId, $query->pageSize, $query->isZb, $query->class, $query->sort, $query->useCount, $query->isBg);
        $return = Tools::getRedis($this->redisDb, $redisKey);
        $pageSize = $query->pageSize;
        if (!$return || !$return['hit']) {
            unset($return);
            if ($query->keyword) {
                $newQuery = $this->queryKeyword($query->keyword);
            }
            if ($query->ratioId > -1) {
                $newQuery['bool']['must'][]['match']['ratio'] = $query->ratioId;
            }
            if ($query->kid) {
                $newQuery['bool']['must'][]['terms']['kid_2'] = $query->kid;
            }
            if ($query->sceneId) {
                $newQuery['bool']['must'][]['terms']['scene_id'] = $query->sceneId;
            }
            if ($query->class) {
                $newQuery['bool']['must'][]['match']['class_id'] = $query->class;
            }

            if ($query->isBg) {
                $newQuery['bool']['must'][]['match']['kid_1'] = 2;
            }
            if ($query->page * $pageSize > 10000) {
                $pageSize = $query->page * $pageSize - 10000;
            }

            if ($query->sort === 'bytime') {
                $sortBy = self::sortByTime();
            } else {
                $sortBy = self::sortDefault();
            }

            if ($query->useCount) {
                $useInfo = AssetUseTop::getLastInfo(2);
                switch ($query->useCount) {
                    case 1:
                        $newQuery['bool']['filter'][]['range']['use_count']['gte'] = $useInfo['top1_count'];
                        break;
                    case 2:
                        $newQuery['bool']['filter'][]['range']['use_count']['lt'] = $useInfo['top1_count'];
                        break;
                }
            } else {
                $useInfo = '';
            }
            try {
                $info = self::find()
                    ->source(['id', 'use_count'])
                    ->query($newQuery)
                    ->orderBy($sortBy)
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
                $return['score'][$value['_id']] = $value['sort'][0];
                if ($useInfo) {
                    if ($value['_source']['use_count'] >= $useInfo['top1_count']) {
                        $return['use_count'][$value['_id']] = 1;
                    } elseif ($value['_source']['use_count'] >= $useInfo['top5_count']) {
                        $return['use_count'][$value['_id']] = 2;
                    } else {
                        $return['use_count'][$value['_id']] = 3;
                    }
                }
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

    public static function sortByTime()
    {
        return 'created desc';
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

    public static function sortByHot()
    {
        return 'edit desc';
    }

    public static function index()
    {
        return 'background2';
    }

    public static function type()
    {
        return 'list';
    }
}
