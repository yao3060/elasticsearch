<?php


namespace app\queries\ES;

class SvgSearchQuery extends BaseTemplateSearchQuery
{
    public function __construct(
        public $keyword = 0,
        public int $kid2 = 0,
        public int $page = 1,
        public int $pageSize = 40,
        public string $sort = 'sort desc'
    ) {
        $this->beforeAssignment();
    }

    /**
     * prepare params
     */
    protected function beforeAssignment()
    {
        $this->keyword = empty($this->keyword) ? 0 : $this->keyword;
    }


    public function query(): array
    {
        $this->queryKeyword()->queryKid2();

        return $this->query;
    }

    public function queryKid2()
    {
        $this->query['bool']['must'][]['terms']['kid_2'] = [$this->kid2];
        return $this;
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




    public function getRedisKey()
    {
        return sprintf(
            'ES_svg2:%s:%s_%d_%d_%d',
            date('Y-m-d'),
            $this->keyword,
            $this->page,
            $this->kid2,
            $this->pageSize
        );
    }
}
