<?php


namespace app\queries\ES;

class SvgSearchQuery extends BaseTemplateSearchQuery
{
    public function __construct(
        public $keyword = 0,
        public array $kid2 = [],
        public int $page = 1,
        public int $pageSize = 40,
        public string $sort = 'sort desc'
    ) {
    }

    public function queryKid2()
    {
        if (!empty($this->kid2)) {
            $this->query['bool']['must'][]['terms']['kid_2'] = $this->kid2;
        }

        return $this;
    }

    public function query(): array
    {
        $this->queryKeyword();

        return $this->query;
    }

    /**
     * @param string $operator
     * @return $this|SvgSearchQuery
     */
    protected function queryKeyword($operator = 'and')
    {
        if (!empty($this->keyword)) {
            $this->query['bool']['must'][]['multi_match'] = [
                'query' => $this->keyword,
                'fields' => ["title^5", "description^1"],
                'type' => 'most_fields',
                "operator" => $operator
            ];
        }

        return $this;
    }

    /**
     * prepare params
     */
    public function beforeAssignment()
    {
        $this->keyword = $this->keyword ?: 0;
        $this->kid2 = $this->kid2 ?: [];
        if (!is_array($this->kid2)) {
            $this->kid2 = [$this->kid2];
        }
    }

    public function getRedisKey()
    {
        $this->beforeAssignment();

        return sprintf(
            'ES_svg2:%s:%s_%d_%s_%d',
            date('Y-m-d'),
            $this->keyword,
            $this->page,
            implode('-', $this->kid2),
            $this->pageSize
        );
    }
}
