<?php


namespace app\queries\ES;


class VideoTemplateSearchQuery extends BaseTemplateSearchQuery
{
    public function __construct(
        public $keyword = "",
        public $classId = [],
        public $page = 1,
        public $pageSize = 40,
        public $ratio = null,
        public $prep = 0,
        public $sort = 'created desc',
        public $offset = 0
    )
    {
    }

    public function beforeAssignment()
    {
        $this->keyword = $this->keyword ? $this->keyword : "";
        $this->classId = $this->classId ? $this->classId : [];
        $this->ratio = $this->ratio != null ? $this->ratio : null;
        if (!is_array($this->classId)) {
            $this->classId = [$this->classId];
        }
        $this->offset = ($this->page - 1) * $this->pageSize;
    }

    public function getRedisKey()
    {
        $this->beforeAssignment();

        $redisKey = "ES_video:excerpt:" . date('Y-m-d') . ":{$this->keyword}_{$this->page}_ " . implode('-', $this->classId) . " _{$this->pageSize}_{$this->ratio}";

        return $redisKey;
    }

    public function queryClassIds()
    {
        if (!empty($this->classId)) {
            foreach ($this->classId as $key) {
                if ($key > 0) {
                    $this->query['bool']['must'][]['terms']['class_id'] = [$this->classId];
                }
            }
        }

        return $this;
    }

    public function query(): array
    {
        $this->queryKeyword($this->keyword)
            ->queryClassIds()->queryRatio();

        return $this->query;
    }
}
