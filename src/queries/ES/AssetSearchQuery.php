<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class AssetSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    public $keyword;
    public $page;
    public $pageSize;
    public $sceneId;
    public $isZb;
    public $sort = 'DESC';
    public $useCount;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $sceneId = 0,
        $isZb = 0,
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
}
