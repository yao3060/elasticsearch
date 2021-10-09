<?php

namespace app\queries\ES;

class TemplateSearchQuery extends BaseTemplateSearchQuery
{
    public $sortClassId;
    public $offset;

    public function __construct(
        public $keyword = null,
        public $page = 1,
        public $kid1 = 0,
        public $kid2 = 0,
        public $sortType = 'default',
        public $tagId = 0,
        public $isZb = 1,
        public $pageSize = 35,
        public $ratio = null,
        public $classId = 0,
        public $update = 0,
        public $size = 0,
        public $fuzzy = 0,
        public $templateTypes = [1, 2],
        public $use = 0,
        public $color = [],
        public $width = 0,
        public $height = 0,
        public $classIntersectionSearch = 0,
        public $elasticsearchColor = ''
    )
    {

    }

    /**
     * deassign
     */
    public function beforeAssignment()
    {
        $this->sortType = $this->sortType ?: 'default';
        $this->keyword = strlen($this->keyword) > 0 ? $this->keyword : null; // 防止为空
        $this->kid1 = $this->kid1 ? $this->kid1 : 0;
        $this->kid2 = $this->kid2 ? $this->kid2 : 0;
        $this->kid2 = $this->kid2 < 5000 ? $this->kid2 : 0;
        $this->tagId = $this->tagId ? $this->tagId : 0;
        $this->isZb = $this->isZb ? $this->isZb : 1;
        $this->ratio = ($this->ratio != null && is_numeric($this->ratio)) ? $this->ratio : null;
        $this->classId = $this->classId ? $this->classId : '0_0_0_0';
        $this->classId = $this->classId != 'undefined' ? $this->classId : '0_0_0_0';
        $this->sortClassId = $this->classId;
        $this->size = $this->size ? $this->size : 0;
        $this->use = $this->use ? $this->use : 0;
        $this->width = $this->width ? $this->width : 0;
        $this->height = $this->height ? $this->height : 0;

        if ($this->keyword == null && !$this->tagId && $this->ratio <= 0 && ($this->classId == '0_0_0_0' || $this->classId == '0_0_0')) {
            //用于排序的class_id但不影响过滤项 影响全局排序的特殊class_id为-1
            $this->sortClassId = -1;
        }

        $this->classId = str_replace(
            ['10_133_0_', '132_133_0_', '10_550_27_'],
            ['31_23_0_', '31_23_0_', '32_27_326_'],
            $this->classId);

    }

    public function isFuzzy()
    {
        return $this->fuzzy == 1;
    }

    /**
     * joint redis key
     */
    public function getRedisKey()
    {
        $this->beforeAssignment();

        $redisKey = "ES_template12-23:";

        if ($this->isFuzzy()) {
            $redisKey .= ":fuzzy";
        }

        $implodeKeyArr = [
            $this->keyword, $this->page, $this->kid1, $this->kid2, $this->sortType,
            $this->tagId, $this->isZb, $this->pageSize,
            $this->ratio, $this->classId, $this->size, $this->use
        ];

        $redisKey .= ":" . implode('_', $implodeKeyArr);

        if ($this->hasTemplateTypes() && is_array($this->templateTypes)) {
            $redisKey .= '_' . implode('|', $this->templateTypes);
        } else if ($this->templateTypes > 0) {
            $redisKey .= "_" . $this->templateTypes;
        }

        if ($this->hasColor()) {
            $redisKey .= '_' . implode(',', array_column($this->color, 'color')) .
                '_' . implode(',', array_column($this->color, 'weight'));
        }

        if ($this->hasWidth()) {
            $redisKey .= "_w={$this->width}";
        }

        if ($this->hasHeight()) {
            $redisKey .= "_h={$this->height}";
        }

        if ($this->hasClassIntersectionSearch()) {
            $redisKey .= "_cis={$this->classIntersectionSearch}";
        }

        return $redisKey;
    }

    /**
     * query offset
     */
    public function queryOffset()
    {
        if ($this->page * $this->pageSize > 10000) {
            $this->pageSize = $this->pageSize - ($this->page * $this->pageSize - 10000) % $this->pageSize;
            $offset = 10000 - $this->pageSize;
        } else {
            $offset = ($this->page - 1) * $this->pageSize;
        }
        return $offset;
    }

    /**
     * query sort type
     */
    public function querySortType()
    {
        switch ($this->sortType) {
            case 'bytime':
                $this->sortByTime();
                break;
            case 'byyesday':
                $this->sortByYesterday();
                break;
            case 'byweekday':
                $this->sortByWeekday();
                break;
            case 'bymonth':
                $this->sortByMonth();
                break;
            case 'byhot':
                $this->sortByHot();
                break;
            default:
                $this->sortDefault($this->keyword, $this->sortClassId);
                break;
        }

        return $this;
    }

    public function queryTemplateTypes()
    {
        if (is_array($this->templateTypes) && count($this->templateTypes) > 1 && in_array(5, $this->templateTypes)) {
            //有H5就添加长页H5(不改变key,除了单独搜索H5类型)
            $this->templateTypes[] = 7;
        }

        if ($this->hasTemplateTypes() && is_array($this->templateTypes)) {
            // 如果搜索全部类型模板则去掉该条件
            if(count($this->templateTypes) < 7){
                $this->query['bool']['must'][]['terms']['template_type'] = $this->templateTypes;
            }
        } else if ($this->templateTypes>0){
            $this->query['bool']['must'][]['match']['template_type'] = $this->templateTypes;
        }

        return $this;
    }

    public function queryTagId()
    {
        if ($this->hasTagId()) {
            $tag_id = explode('_', $this->tagId);
            foreach ($tag_id as &$item) {
                $item = (int)$item;
            }
            $this->query['bool']['filter'][]['terms']['tag_id'] = $tag_id;
        }

        return $this;
    }

    /**
     * return query
     */
    public function query(): array
    {
        $this->offset = $this->queryOffset();

        $this->queryKeyword()
            ->queryKid1()
            ->queryKid2()
            ->queryRatio()
            ->queryClassIds()
            ->queryTemplateTypes()
            ->queryTagId()
            ->queryIsZb()
            ->queryIosAlbumUser()
            ->queryWidth()
            ->queryHeight()
            ->querySortType();

        //颜色搜索
        if ($this->hasColor()) {
            return $this->formatColor(array_column($this->color, 'color'), array_column($this->color, 'weight'));
        }

        return $this->query;
    }
}
