<?php


namespace app\queries\ES;


class LottieVideoSearchQuery extends BaseTemplateSearchQuery
{
    public $offset = 0;
    public $sort = 'create_date desc';

    public function __construct(
        public $keyword = 0,
        public $classId = [],
        public $page = 1,
        public $pageSize = 40,
        public $prep = 0
    ) {
        $this->beforeAssignment();
    }

    public function query(): array
    {
        $this->queryKeyword()->queryClassId();

        return $this->query;
    }

    public function queryClassId()
    {
        if (!empty($this->classId)) {
            foreach ($this->classId as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                }
            }
        }

        return $this;
    }

    public function queryKeyword($isOr = false)
    {
        if (!empty($this->keyword)) {
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

    public function getRedisKey()
    {
        $redisKey = "ES_video:lottie:" . date('Y-m-d') . ":{$this->keyword}_{$this->page}_ " . implode(
            '-',
            $this->classId
        ) . " _{$this->pageSize}";

        return $redisKey;
    }

    public function beforeAssignment()
    {
        $this->keyword = $this->keyword ?: 0;
        $this->classId = $this->classId ?: [];
        if (!is_array($this->classId)) {
            $this->classId = [$this->classId];
        }
        $this->offset = ((int)$this->page - 1) * (int) $this->pageSize;
    }
}
