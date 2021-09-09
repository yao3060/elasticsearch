<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;
use Yii;

class SvgSearchQuery implements QueryBuilderInterface
{
    private array $query = [];

    public function __construct(
        public $keyword = 0,
        public array $kid2 = [],
        public int $page = 1,
        public int $pageSize = 40,
        public string $sort = 'sort desc'
    ) {
    }

    public function query(): array
    {
        $this->queryKeyword();
        if (!empty($this->kid2)) {
            $this->query['bool']['must'][]['terms']['kid_2'] = $this->kid2;
        }
        Yii::info($this->query);
        return $this->query;
    }

    /**
     * create query function
     *
     * @param string $keyword
     * @param Enum $operator="and,or"
     * @return void
     */
    public function queryKeyword($operator = 'and')
    {
        $this->query['bool']['must'][]['multi_match'] = [
            'query' => $this->keyword,
            'fields' => ["title^5", "description^1"],
            'type' => 'most_fields',
            "operator" => $operator
        ];
    }

    public function getRedisKey()
    {
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
