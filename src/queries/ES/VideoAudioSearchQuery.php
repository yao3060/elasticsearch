<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class VideoAudioSearchQuery implements QueryBuilderInterface
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
    public int $parentsId;
    public string $classId;
    public int $prep;
    public int $isDesigner;
    public int $isVip;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $parentsId = 0,
        $classId = [],
        $prep = 0,
        $isDesigner = 0,
        $isVip = 0
    )
    {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->parentsId = $parentsId;
        $this->classId = $classId;
        $this->prep = $prep;
        $this->isDesigner = $isDesigner;
        $this->isVip = $isVip;
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
