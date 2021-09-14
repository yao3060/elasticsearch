<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class VideoESearchQuery implements QueryBuilderInterface
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
    public string|array $classId;
    public int $ratio;
    public string $scopeType;
    public int $owner;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $classId = 0,
        $ratio = 0,
        $scopeType = 0,
        $owner = 0,
    )
    {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->classId = $classId;
        $this->ratio = $ratio;
        $this->scopeType = $scopeType;
        $this->owner = $owner;
    }

    public function query(): array
    {
        if ($this->keyword) {
            $newQuery = $this->queryKeyword($this->keyword);
        }
        if ($this->classId && $this->classId != 0) {
            foreach ($this->classId as $key) {
                if ($key > 0) {
                    $newQuery['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }
        //1横2竖
        if ($this->ratio == 1) {
            $newQuery['bool']['filter']['script']['script'] = [
                'source' => 'doc["width"].value>doc["height"].value',
                "lang" => "painless"
            ];
        } elseif ($this->ratio == 2) {
            $newQuery['bool']['filter']['script']['script'] = [
                'source' => 'doc["height"].value>doc["width"].value',
                "lang" => "painless"
            ];
        } elseif ($this->ratio == 3) {
            $newQuery['bool']['filter']['script']['script'] = [
                'source' => 'doc["height"].value == doc["width"].value',
                "lang" => "painless"
            ];
        }
        $newQuery['bool']['must'][]['match']['scope_type'] = $this->scopeType;
        if (!empty($this->owner) && $this->scopeType == 'bg') {
            // 匹配度，避免or没有结果时查询全部条件
            $newQuery['bool']['minimum_should_match'] = 1;
            // 设计师自身包含待审核以及审核通过部分
            $boolMust = [];
            $boolMust[]['term']['owner'] = $this->owner;
            $boolMust[]['terms']['audit_through'] = [2, 3, 4];
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
    public static function sortByTime()
    {
        return 'create_date desc';
    }
    public function pageSizeSet(){
        $pageSize = $this->pageSize;
        if ($this->page * $this->pageSize > 10000) {
            $pageSize = $this->page * $pageSize - 10000;
        }
        return $pageSize;
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
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
    }
}
