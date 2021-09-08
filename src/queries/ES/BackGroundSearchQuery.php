<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class BackGroundSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    public string $keyword;
    public int $page;
    public int $pageSize;
    public string $sceneId;
    public int $isZb;
    public string $sort;
    public int $useCount;
    public string $kid;
    public int $ratioId;
    public int $class;
    public int $isBg;
    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $sceneId = 0,
        $isZb = 1,
        $sort = 'DESC',
        $useCount = 0,
        $kid = 0,
        $ratioId = 0,
        $class = 0,
        $isBg = 0
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->sceneId = $sceneId;
        $this->isZb = $isZb;
        $this->sort = $sort;
        $this->useCount = $useCount;
        $this->kid = $kid;
        $this->ratioId = $ratioId;
        $this->class = $class;
        $this->isBg = $isBg;
    }
    public function query():array
    {
        return ['my', 'query'];
    }
    public function getRedisKey()
    {
        // TODO: Implement getRedisKey() method.
    }
}
