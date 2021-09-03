<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class AssetSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    private $keyword;
    public $page;
    public $pageSize;
    private $tag_id;
    private $isZb;
    public $sort = 'DESC';
    private $use_count;

    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $tag_id = [],
        $isZb = 0,
        $sort = [],
        $use_count = []
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->tag_id = $tag_id;
        $this->isZb = $isZb;
        $this->sort = $sort;
        $this->use_count = $use_count;

    }
    public function query():array
    {
        return ['my', 'query'];
    }
}
