<?php

namespace app\queries\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\ES\Template;

class TemplateSearchQuery extends BaseTemplateSearchQuery
{
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
        public $elasticsearchColor = '',
        public $sortClassId,
        public $offset,
        protected $query = []
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
     * query class_ids
     */
    public function queryClassIds()
    {
        if ($this->classId) {
            $classId = explode('_', $this->classId);
            //class_id 过滤 为空 或者 不是数字 或者 小于等于0 都从分类arr中剔除
            foreach ($classId as $key => $item) {
                if (empty($item) || !is_numeric($item) || $item <= 0) {
                    unset($classId[$key]);
                }
            }
            $classId = array_values($classId);

            if (in_array(760, $classId)) {
                //视频模板要特殊处理
                $this->templateTypes = 4;
            }
            //true 采用交集方式查询 即分类有交集就能查询出来
            if (!empty($this->classIntersectionSearch)) {
                //剔除为0的项
                $classId = array_diff($classId, [0, '']);
                if (!empty($classId)) {
                    $this->query['bool']['must'][]['terms']['class_id'] = $classId;
                }
            } else {
                foreach ($classId as $key) {
                    if ($key > 0) {
                        $this->query['bool']['must'][]['terms']['class_id'] = [$key];
                    }
                }
            }
        } else {
            if ($this->kid1 == 1) {
                $this->query['bool']['must_not'][]['terms']['class_id'] = ['437', '760', '290', '810', '902'];
            }
        }

        return $this;
    }

    /**
     * query width
     */
    public function queryWidth()
    {
        if (!empty($this->width)) {
            $this->query['bool']['filter'][]['match']['width'] = $this->width;
        }

        return $this;
    }

    /**
     * query height
     */
    public function queryHeight()
    {
        if (!empty($this->width)) {
            $this->query['bool']['filter'][]['match']['width'] = $this->width;
        }

        return $this;
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
                $this->sort = Template::sortDefault($this->keyword, $this->sortClassId);
                break;
        }

        return $this;
    }

    /**
     * query color
     */
    public function queryColor()
    {
        list($colorRange, $colorParams) = self::formatColor(array_column($this->color, 'color'), array_column($this->color, 'weight'));
        $this->query['bool']['filter'][]['range']['r'] = [
            'from' => $colorRange[0]['from'],
            'to' => $colorRange[0]['to']
        ];
        $this->query['bool']['filter'][]['range']['g'] = [
            'from' => $colorRange[1]['from'],
            'to' => $colorRange[1]['to']
        ];
        $this->query['bool']['filter'][]['range']['b'] = [
            'from' => $colorRange[2]['from'],
            'to' => $colorRange[2]['to']
        ];

        return [
            'function_score' => [
                'query' => $this->query,
                'functions' => [
                    [
                        'script_score' => [
                            'script' => [
                                'inline' => 'colorsort',
                                'lang' => 'native',
                                'params' => [
                                    'center' => $colorParams,
                                    'distance' => 200   //图像相似度,可以根据具体搜索词进行调节
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * return query
     */
    public function query(): array
    {
        $this->offset = $this->queryOffset();

        $this->queryKid1()
            ->queryKid2()
            ->queryRatio()
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
