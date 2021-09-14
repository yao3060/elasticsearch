<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\Assettaglink;
use app\models\AssetUseTop;

/**
 * Class Asset
 * @package app\models\ES
 * author  ysp
 */
class Asset extends BaseModel
{
    private $redisDb = 8;

    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-03
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = Tools::getRedis($this->redisDb, $query->getRedisKey());
        if (!$return || !$return['hit']) {
            unset($return);
            if ($query->useCount) {
                $useInfo = AssetUseTop::getLastInfo(1);
            } else {
                $useInfo = '';
            }
            try {
                $info = self::find()
                    ->source(['id', 'use_count'])
                    ->query($query->query())
                    ->orderBy($query->sortBy())
                    ->offset(($query->page - 1) * $query->pageSet())
                    ->limit($query->pageSet())
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
            Tools::setRedis($this->redisDb, $query->getRedisKey(), $return, 86400);
        }
        return $return;
    }

    //推荐搜索
    public function recommendSearch(QueryBuilderInterface $query): array
    {
        if ($query->keyword) {
            $newQuery['bool']['must']['match']['title'] = $query->keyword;
            $newQuery['bool']['filter'][]['term']['kid_1'] = 1;
        }
        try {
            $info = self::find()
                ->source(['id'])
                ->query($newQuery)
                //                ->orderBy($sort)
                ->offset(($query->page - 1) * $query->pageSize)
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
            $return['ids'][] = $value['_id'];
            if (isset($value['sort'])) {
                $return['score'][$value['_id']] = $value['sort'][0];
            } else {
                $return['score'][$value['_id']] = 0;
            }
        }
        return $return;
    }

    public function saveRecord($fields = [])
    {
        if (!$fields['id']) return false;
        $info = self::findOne($fields['id']);
        if (!$info) {
            $info = new self();
            $info->primaryKey = $fields['id'];
        }
        $info->id = $fields['id'];
        $info->title = $fields['title'];
        $info->description = $fields['description'];
        $info->created = $fields['created'];
        $info->kid_1 = (int)$fields['kid_1'];
        $info->kid_2 = (int)$fields['kid_2'];
        $info->kid_3 = (int)$fields['kid_3'];
        $info->pr = $fields['pr'] ? (int)$fields['pr'] : 0;
        $info->man_pr = $fields['man_pr'] ? (int)$fields['man_pr'] : 0;
        $info->man_pr_add = $fields['man_pr_add'] ? (int)$fields['man_pr_add'] : 0;
        $info->width = $fields['width'] ? (int)$fields['width'] : 0;
        $info->height = $fields['height'] ? (int)$fields['height'] : 0;
        $info->ratio = $fields['ratio'] ? (int)$fields['ratio'] : 0;
        $scene = [];
        $find_tags = Assettaglink::find()->select('tag_id')->where(['asset_id' => $fields['id']])->all();
        foreach ($find_tags as $key => $value) {
            $scene[] = (int)$value['tag_id'];
        }
        $info->scene_id = $scene;
        $info->save();
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

    public static function index()
    {
        return 'asset2';
    }

    public static function type()
    {
        return 'list';
    }
}
