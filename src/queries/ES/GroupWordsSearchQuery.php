<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class GroupWordsSearchQuery implements QueryBuilderInterface
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
    public string $search;
    public string $searchAll;
    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $search = 0,
        $searchAll = 0,
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->search = $search;
        $this->searchAll = $searchAll;
    }

    public function query(): array
    {
        return ['my', 'query'];
    }

    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
    }
}
