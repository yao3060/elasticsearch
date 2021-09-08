<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\service\designers\DesignerRecommendAssetTagService;

class GroupWords extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

    public static function index() {
        return 'group_word';
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
        $redisKey = "ES_group_word:" . date('Y-m-d') . ":{$query->keyword}_{$query->page}_"  . "_{$query->pageSize}";
        if(!empty($query->search)) {
            $redisKey .= '_'.$query->search;
        }
        if(!empty($query->searchAll)) {
            $redisKey .= '_'.$query->searchAll.'_v1';
        }
        $return = Tools::getRedis($this->redisDb, $redisKey);
        $pageSize = $query->pageSize;
        if (!$return) {
            if($query->searchAll) {
                $keyword = DesignerRecommendAssetTagService::getRecommendAssetKws(5);
                $shouldMatch = [];
                foreach ($keyword as $keywordVal) {
                    $shouldMatch[] = [
                        'match' => [
                            'keyword' => $keywordVal
                        ]
                    ];
                }
                $newQuery['bool']['should'][] = $shouldMatch;
            } elseif ($query->keyword) {
                $newQuery = $this->queryKeyword($query->keyword, false, true);
            }
            if(!empty($query->search)) {
                $newQuery['bool']['must'][]['multi_match'] = [
                    'query' => $query->search,
                    'fields' => ["keyword^1"],
                    'type' => 'most_fields',
                    "operator" => 'and'
                ];
            }

            $sort = $this->sortByHot();
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
                $return['score'][$value['_id']] = $value['sort'][0];
            }
            Tools::setRedis($this->redisDb, $redisKey, $return, 86400);
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


}
