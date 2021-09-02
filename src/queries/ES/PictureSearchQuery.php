<?php


namespace app\queries\ES;

use app\interfaces\ES\QueryBuilderInterface;

class PictureSearchQuery implements QueryBuilderInterface
{
    public $page;
    public $pageSize;
    public $sort = 'DESC';

    private $kid2;
    private $sceneId;
    private $ratioId;
    private $isZb;
    private $vipPic;

    function __construct(
        $keyword = 0,
        $page = 1,
        $kid2 = [],
        $sceneId = [],
        $ratioId = [],
        $pageSize = 40,
        $isZb = 0,
        $vipPic = 0
    ) {
        $this->keyword = $keyword;
        $this->page = $page;
        $this->kid2 = $kid2;
        $this->scene_id = $sceneId;
        $this->ratio_id = $ratioId;
        $this->pageSize = $pageSize;
        $this->isZb = $isZb;
        $this->vipPic = $vipPic;
    }

    public function query(): array
    {
        return ['my', 'query'];
    }
}
