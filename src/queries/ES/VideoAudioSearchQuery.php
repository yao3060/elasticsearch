<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class VideoAudioSearchQuery implements QueryBuilderInterface
{
    /**
     * @var string|int|mixed 关键字
     */
    public string $keyword;
    /**
     * @var int|mixed 页码
     */
    public int $page;
    /**
     * @var int|mixed 每页数量
     */
    public int $pageSize;
    public int $parentsId;
    public string $classId;
    public int $prep;
    public int $isDesigner;
    public int $isVip;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $parentsId = 0,
        $classId = [],
        $prep = 0,
        $isDesigner = 0,
        $isVip = 0
    )
    {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->parentsId = $parentsId;
        $this->classId = $classId;
        $this->prep = $prep;
        $this->isDesigner = $isDesigner;
        $this->isVip = $isVip;
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($this->classId) {
            foreach ($this->classId as $key) {
                if ($key > 0) {
                    $newQuery['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }
        $newQuery['bool']['must'][]['match']['parents_id'] = $this->parentsId;
        if ($this->isDesigner == 1) {
            $newQuery['bool']['must'][]['term']['is_vip'] = 0;
        }
        if ($this->isVip == 1) {
            $newQuery['bool']['must'][]['term']['is_vip'] = 1;
        }
        return $newQuery;
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
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
        $redisKey = sprintf(
            'ES_video:audio:%s:%d_%s_%d_%d',
            date('Y-m-d'),
            $this->parentsId,
            $this->keyword,
            $this->page,
            $this->pageSize
        );
        return $redisKey;
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
    }

    public function sortBy()
    {
        if ($this->isVip == 1) {
            $newQuery['bool']['must'][]['term']['is_vip'] = 1;
            $sort = $this->sortByOrderTime();
        } else {
            $sort = $this->sortByTime();
        }
        if ($this->isDesigner == 0 && $this->isVip == 0) {  // 用户视频编辑器原版音乐排版使用pr排序
            $sort = $this->sortByPr();
        }
        return $sort;
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

    public function queryOffset()
    {
        if ($this->page * $this->pageSize > 10000) {
            $this->pageSize = $this->pageSize - ($this->page * $this->pageSize - 10000) % $this->pageSize;
            $offset = 10000 - $this->pageSize;
        } else {
            $offset = ($this->page - 1) * $this->pageSize;
        }
        return $offset;
    }
}
