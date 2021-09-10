<?php

namespace app\models\ES;

use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;

/**
 * @package app\models\ES
 * author  ysp
 */
class VideoAudio extends BaseModel
{
    /**
     * @var int redis
     */
    private $redisDb = 8;

    public static function index()
    {
        return 'video_audio';
    }

    public static function type()
    {
        return 'list';
    }

    public static function sortByTime()
    {
        return 'create_date desc';
    }

    public static function sortByOrderTime()
    {
        return 'create_date asc';
    }

    public static function sortByPr()
    {
        return 'pr desc';
    }

    /**
     * @param QueryBuilderInterface $query
     * @return array 2021-09-08
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $redisKey = sprintf(
            'ES_video:audio:%s:%d_%s_%d_%d',
            date('Y-m-d'),
            $query->parentsId,
            $query->keyword,
            $query->page,
            $query->pageSize
        );
        $return = Tools::getRedis($this->redisDb, $redisKey);
        if (!$return || $query->prep) {
            unset($return);
            if ($query->keyword) {
                $newQuery = $this->queryKeyword($query->keyword);
            }
            if ($query->classId) {
                foreach ($query->classId as $key) {
                    if ($key > 0) {
                        $newQuery['bool']['must'][]['terms']['class_id'] = [$key];
                    }
                }
            }
            $newQuery['bool']['must'][]['match']['parents_id'] = $query->parentsId;
            if ($query->isDesigner == 1) {
                $newQuery['bool']['must'][]['term']['is_vip'] = 0;
            }
            if ($query->isVip == 1) {
                $newQuery['bool']['must'][]['term']['is_vip'] = 1;
                $sort = $this->sortByOrderTime();
            } else {
                $sort = $this->sortByTime();
            }
            if ($query->isDesigner == 0 && $query->isVip == 0) {  // 用户视频编辑器原版音乐排版使用pr排序
                $sort = $this->sortByPr();
            }
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
