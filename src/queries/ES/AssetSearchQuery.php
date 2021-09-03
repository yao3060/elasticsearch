<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class AssetSearchQuery implements QueryBuilderInterface
{
    //搜索所需要参数
    public $key_word;
    public $page;
    public $pageSize;
    public $sceneId;
    public $isZb;
    public $sort = 'DESC';
    public $use_count;

    function __construct(
        $key_word = 0,
        $page = 1,
        $pageSize = 40,
        $sceneId = 0,
        $isZb = 0,
        $sort = 'DESC',
        $use_count = 0
    ) {
        $this->key_word = $key_word;
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->sceneId = $sceneId;
        $this->isZb = $isZb;
        $this->sort = $sort;
        $this->use_count = $use_count;

    }
    public function query():array
    {
        return ['my', 'query'];
    }
}
