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

    public static function index()
    {
        return 'video_e_inx';
    }

    public static function type()
    {
        return 'list';
    }

    public static function sortByHot()
    {
        return 'sort desc';
    }

    public function attributes()
    {
        return ['id', 'title', 'create_date', 'pr', 'width', 'height', 'class_id', 'description', 'owner', 'audit_through', 'scope_type'];
    }



    /**
     * @param \app\queries\ES\VideoESearchQuery $query
     * @return array 2021-09-10
     * return ['hit','ids','score'] 命中数,命中id,模板id=>分数
     */
    public function search(QueryBuilderInterface $query): array
    {
        $return = '';
        if (!$return) {
            unset($return);
            try {
                $info = self::find()
                    ->source(['id'])
                    ->query($query->query())
                    ->orderBy($query->sortByTime())
                    ->offset($query->queryOffset())
                    ->limit($query->pageSizeSet())
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
        }
        return $return;
    }

    //推荐搜索
   /* public function recommendSearch(QueryBuilderInterface $query): array
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
    }*/
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
