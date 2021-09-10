<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class VideoESearchQuery implements QueryBuilderInterface
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
    public string | array $classId;
    public int $ratio;
    public string $scopeType;
    public int $owner;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $classId = 0,
        $ratio = 0,
        $scopeType= 0,
        $owner = 0,
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->classId = $classId;
        $this->ratio = $ratio;
        $this->scopeType = $scopeType;
        $this->owner = $owner;
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
