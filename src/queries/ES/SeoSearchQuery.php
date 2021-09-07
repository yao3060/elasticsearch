<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class SeoSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    public $keyword;
    public $pageSize;
    function __construct(
        $keyword = 0,
        $pageSize = 11
    ) {
        $this->keyword = $keyword;
        $this->pageSize = $pageSize;
    }
    public function query():array
    {
        return ['my', 'query'];
    }
}
