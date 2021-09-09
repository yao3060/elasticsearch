<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class PictureSearchQuery implements QueryBuilderInterface
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
    public array | string $sceneId;
    public int $isZb;
    public array | string $kid;
    public int  $vipPic;
    public int $ratioId;
    function __construct(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $sceneId = [],
        $isZb = 1,
        $kid = [],
        $vipPic = 0,
        $ratioId = 0
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->kid = $kid;
        $this->sceneId = $sceneId;
        $this->ratioId = $ratioId;
        $this->pageSize = $pageSize;
        $this->isZb = $isZb;
        $this->vipPic = $vipPic;
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
