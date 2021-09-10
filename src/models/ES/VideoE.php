<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\services\designers\DesignerRecommendAssetTagService;

/**
 * @package app\models\ES
 * author  ysp
 */
class VideoE extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

    public static function index() {
        return 'video_e_inx';
    }
    public static function type() {
        return 'list';
    }
    public static function sortByHot() {
        return 'sort desc';
    }
    public function attributes() {
        return ['id', 'title', 'create_date', 'pr', 'width', 'height', 'class_id','description', 'owner', 'audit_through', 'scope_type'];
    }
    public static function sortByTime() {
        return 'create_date desc';
    }
    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-10
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        /*$redis_key = "ES_video_e:video_e:" . date('Y-m-d') . ":{$keyword}_{$page}_ " . implode('-', $class_id) . " _{$pageSize}". " _{$ratio}"."_{$scopeType}"."_{$owner}";
        $return = Tools::getRedis(self::$redis_db, $redis_key);*/
        $return = '';
        if (!$return) {
            unset($return);
            if ($query->keyword) {
                $newQuery = $this->queryKeyword($query->keyword);
            }
            if ($query->classId && $query->classId != 0) {
                foreach ($query->classId  as $key) {
                    if ($key > 0) {
                        $newQuery['bool']['must'][]['terms']['class_id'] = [$key];
                    }
                }
            }

            //1横2竖
            if ($query->ratio == 1) {
                $newQuery['bool']['filter']['script']['script'] = [
                    'source' => 'doc["width"].value>doc["height"].value',
                    "lang" => "painless"
                ];
            } elseif ($query->ratio == 2) {
                $newQuery['bool']['filter']['script']['script'] = [
                    'source' => 'doc["height"].value>doc["width"].value',
                    "lang" => "painless"
                ];
            } elseif($query->ratio == 3) {
                $newQuery['bool']['filter']['script']['script'] = [
                    'source' => 'doc["height"].value == doc["width"].value',
                    "lang" => "painless"
                ];
            }

            $newQuery['bool']['must'][]['match']['scope_type'] = $query->scopeType;

            if(!empty($query->owner) && $query->scopeType == 'bg') {
                // 匹配度，避免or没有结果时查询全部条件
                $newQuery['bool']['minimum_should_match'] = 1;
                // 设计师自身包含待审核以及审核通过部分
                $boolMust = [];
                $boolMust[]['term']['owner'] = $query->owner;
                $boolMust[]['terms']['audit_through'] = [2,3,4];
                $newQuery['bool']['should'][]['bool']['must'] = $boolMust;

                // 全部审核通过
                $newQuery['bool']['should'][] = [
                    'term' => [
                        'audit_through' => 4
                    ]
                ];
            } else {
                $newQuery['bool']['must'][]['term']['audit_through'] = 4;
            }

            $sort = $this->sortByTime();
            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($newQuery)
                    ->orderBy($sort)
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
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            //Tools::setRedis(self::$redis_db, $redis_key, $return, 86400);
        }
        return $return;
    }
    //推荐搜索
    public function recommendSearch(QueryBuilderInterface $query): array
    {
        if ($query->keyword) {
            $newQuery = $this->queryKeyword($query->keyword, true);
        }
        $sort = $this->sortDefault();
        try {
            $info = self::find()
                ->source(['id'])
                ->query($newQuery)
                ->orderBy($sort)
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
            $return['score'][$value['_id']] = $value['sort'][0];
        }
        return $return;
    }
    public static function queryKeyword($keyword, $is_or = false) {
        $operator = $is_or ? 'or' : 'and';
        $query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => ["title^5", "description^1"],
            'type' => 'most_fields',
            "operator" => $operator
        ];
        return $query;
    }
    public static function sortDefault() {
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
