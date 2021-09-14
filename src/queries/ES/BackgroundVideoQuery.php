<?php


namespace app\queries\ES;

class BackgroundVideoQuery extends BaseTemplateSearchQuery
{
    protected $query = [];

    public function __construct(
        public $keyword = 0,
        public $classId = [],
        public $page = 1,
        public $pageSize = 1,
        public $ratio = 0,
        public $sort = 'create_date desc',
        public $offset = 0
    )
    {

    }

    public function getRedisKey()
    {
        $this->keyword = $this->keyword ?: 0;

        $this->classId = $this->classId ?: [];

        if (!is_array($this->classId)) {
            $this->classId = [$this->classId];
        }

        $this->offset = ((int)$this->page - 1) * (int)$this->pageSize;

        $redisKey = "ES_video:bg_video:" . date('Y-m-d') . ":{$this->keyword}_{$this->page}_ "
            . implode('-', $this->classId) . " _{$this->pageSize}" . " _{$this->ratio}";

        return $redisKey;
    }

    protected function queryKeyword()
    {
        if (!empty($this->keyword)) {
            $operator = $this->fuzzy ? 'or' : 'and';
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^5", "description^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }
        return $this;
    }

    public function queryRatio()
    {
        //1横2竖
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

    public function queryClassIds()
    {
        if ($this->classId) {
            foreach ($this->classId as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }

        return $this;
    }

    public function query(): array
    {
        $this->queryKeyword()->queryClassIds()->queryRatio();

        return $this->query;
    }
}
