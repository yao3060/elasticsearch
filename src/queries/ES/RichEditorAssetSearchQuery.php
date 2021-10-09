<?php


namespace app\queries\ES;


class RichEditorAssetSearchQuery extends BaseTemplateSearchQuery
{
    protected $query = [];

    public function __construct(
        public $keyword = 0,
        public $classId = [],
        public $page = 1,
        public $pageSize = 40,
        public $ratio = 0,
        public $sort = 'create_date desc'
    )
    {
        $this->beforeAssignment();
    }

    public function queryKeyword($is_or = false)
    {
        if (!empty($this->keyword)) {
            $operator = $is_or ? 'or' : 'and';
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^5", "description^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }
        return $this;
    }

    public function queryClassIds()
    {
        if (is_array($this->classId)) {
            foreach ($this->classId as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        } else {
            $this->query['bool']['must'][]['terms']['class_id'] = [$this->classId];
        }

        return $this;
    }

    public function queryRatio()
    {
        if ($this->ratio == 1) {
            $this->query['bool']['filter']['script']['script'] = [
                'source' => 'doc["width"].value>=doc["height"].value',
                "lang" => "painless"
            ];
        } elseif ($this->ratio == 2) {
            $this->query['bool']['filter']['script']['script'] = [
                'source' => 'doc["height"].value>=doc["width"].value',
                "lang" => "painless"
            ];
        }
        return $this;
    }

    public function beforeAssignment()
    {
        $this->keyword = $this->keyword ?: 0;
        $this->classId = $this->classId ?: [];
        if (!is_array($this->classId)) {
            $this->classId = [$this->classId];
        }
        $this->page = $this->page ?: 1;
    }

    public function query(): array
    {
        $this->queryKeyword()
            ->queryClassIds()
            ->queryRatio();

        return $this->query;
    }

    public function getRedisKey()
    {
        $redisKey = "ES_RT:rt_asset:" . date('Y-m-d') . ":{$this->keyword}_{$this->page}_ " . implode('-', $this->classId) . " _{$this->pageSize}" . " _{$this->ratio}";

        return $redisKey;
    }
}
