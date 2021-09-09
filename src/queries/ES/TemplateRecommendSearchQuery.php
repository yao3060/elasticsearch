<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class TemplateRecommendSearchQuery extends BaseTemplateSearchQuery
{
    public function __construct(
        public $keyword = 0,
        public $page = 1,
        public $pageSize = 40,
        public $templateType = null,
        public $ratio = null,
        protected $query = []
    )
    {
    }

    /**
     * deassign
     */
    public function beforeAssignment()
    {
        $this->keyword = $this->keyword ? $this->keyword : 0;
        $this->templateType = ($this->templateType != null && is_numeric($this->templateType)) ? $this->templateType : null;
        $this->ratio = ($this->ratio != null && is_numeric($this->ratio)) ? $this->ratio : null;
    }


    /**
     * joint redis key
     * @return string
     */
    public function getRedisKey()
    {
        $this->beforeAssignment();

        $implodeKeys = [
            $this->keyword, $this->page, $this->pageSize,
            $this->templateType, $this->ratio
        ];

        return "ESTemplate:recommendSearch:" . implode('-', $implodeKeys);
    }

    /**
     * query keyword
     */
    public function queryKeyword()
    {
        if ($this->keyword) {
            $this->query['bool']['must'][]['match']['title'] = $this->keyword;
        }
        return $this;
    }

    /**
     * query template_type
     */
    public function queryTemplateType()
    {
        if ($this->templateType != null) {
            $this->query['bool']['must'][]['match']['template_type'] = $this->templateType;
        }
        return $this;
    }

    /**
     * query ratio
     */
    public function queryRatio()
    {
        if ($this->ratio != null) {
            $this->query['bool']['must'][]['match']['ratio'] = $this->ratio;
        }
        return $this;
    }

    /**
     * return query
     */
    public function query(): array
    {
        $this->queryKeyword()->queryTemplateType()->queryRatio();

        return $this->query;
    }
}
