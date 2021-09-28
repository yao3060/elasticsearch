<?php

namespace app\queries\ES;

use app\models\ES\Template;

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

    /**
     * joint redis key
     */
    public function getRedisKey()
    {
        $this->beforeAssignment();

        $redisKey = "ES_template12-23:";
        if ($this->fuzzy == 1) {
            $redisKey .= ":fuzzy";
        }

        $implodeKeyArr = [
            $this->keyword, $this->page, $this->kid1, $this->kid2, $this->sortType,
            $this->tagId, $this->isZb, $this->pageSize,
            $this->ratio, $this->classId, $this->size, $this->use
        ];

        $redisKey .= ":" . implode('_', $implodeKeyArr);

        if (!empty($this->templateTypes) && is_array($this->templateTypes)) {
            $redisKey .= '_' . implode('|', $this->templateTypes);
        } else if ($this->templateTypes > 0) {
            $redisKey .= "_" . $this->templateTypes;
        }
        if ($this->color) {
            $redisKey .= '_' . implode(',', array_column($this->color, 'color')) .
                '_' . implode(',', array_column($this->color, 'weight'));
        }
        if (!empty($this->width)) {
            $redisKey .= "_w={$this->width}";
        }
        if (!empty($this->height)) {
            $redisKey .= "_h={$this->height}";
        }
        if (!empty($this->classIntersectionSearch)) {
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
                $this->sort = Template::sortByTime();
                break;
            case 'byyesday':
                $this->sort = Template::sortByYesday();
                $this->query['bool']['filter'][]['range']['web_dl']['gt'] = 0; //获取昨日有下载量的模板
                break;
            case 'byweekday':
                $this->sort = Template::sortByWeekday();
                $this->query['bool']['filter'][]['range']['week_web_dl']['gt'] = 0;
                break;
            case 'bymonth':
                $this->sort = Template::sortByMonth();
                $this->query['bool']['filter'][]['range']['month_web_dl']['gt'] = 0;
                break;
            case 'byhot':
                $this->sort = Template::sortByHot();
                break;
            default:
                $this->sort = $this->sortDefault($this->keyword, $this->sortClassId);
                break;
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
            ->queryTagIds()
            ->queryIsZb()
            ->queryIosAlbumUser()
            ->queryWidth()
            ->queryHeight()
            ->querySortType();

        //颜色搜索
        if ($this->color) {
            return $this->formatColor(array_column($this->color, 'color'), array_column($this->color, 'weight'));
        }

        return $this->query;
    }
}
