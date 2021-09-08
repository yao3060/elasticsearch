<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class AssetSearchQuery implements QueryBuilderInterface
{
    //搜索所需 要参数
    public string $keyword;
    public int $page;
    public int $pageSize;
    public int $sceneId;
    public int $isZb;
    public string $sort;
    public int $useCount;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $sceneId = 0,
        $isZb = 1,
        $sort = 'DESC',
        $useCount = 0
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->sceneId = $sceneId;
        $this->isZb = $isZb;
        $this->sort = $sort;
        $this->useCount = $useCount;

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
