<?php


namespace app\queries\ES;

class BackgroundVideoQuery extends BaseTemplateSearchQuery
{
    public $keyword;
    public $classId;
    public $page;
    public $pageSize;
    public $ratio;
    public $sort;
    protected $query = [];

    public function __construct(
        $params
    )
    {
        $this->keyword = $params['keyword'] ?? 0;
        $this->classId = $params['class_id'] ?? [];
        $this->page = $params['page'] ?? 1;
        $this->pageSize = $params['page_size'] ?? 40;
        $this->ratio = $params['ratio'] ?? 0;
    }

    public function getRedisKey()
    {
        $this->keyword = $this->keyword ?: 0;

        if (!is_array($this->classId)) {
            $this->classId = [$this->classId];
        }

        $redis_key = "ES_video:bg_video:" . date('Y-m-d') . ":{$this->keyword}_{$this->page}_ " . implode('-', $this->classId) . " _{$this->pageSize}" . " _{$this->ratio}";

        return $redis_key;
    }

    public function queryKeyword($isOr = 'and')
    {
        if ($this->keyword) {
            $operator = $isOr ? 'or' : 'and';
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

    public function sortByTime()
    {
        $this->sort = 'create_date desc';
    }

    public function query(): array
    {
        $redisKey = $this->getRedisKey();

        $this->sortByTime();
        $this->queryKeyword()->queryClassIds()->queryRatio();

        return ['redis' => $redisKey];
    }
}
