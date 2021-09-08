<?php

namespace app\queries\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\ES\Template;

class TemplateSearchQuery extends BaseTemplateSearchQuery
{
    /**
     * 搜索关键词
     * @var string|mixed
     */
    public $keyword;

    /**
     * 一级版式
     * @var int|string|mixed
     */
    public $kid1;

    /**
     * 二级版式
     * @var int|string|mixed
     */
    public int|string $kid2;

    /**
     * 排序  byyesday：昨日热门 ；bymonth：热门下载；bytime：最新上传
     * @var string|mixed
     */
    public string $sortType = 'default';

    /**
     * 风格
     * @var int|string|mixed
     */
    public int|string $tagId = 0;

    /**
     * 是否可商用   >= 1 可商用
     * @var bool|mixed
     */
    public int $isZb = 1;

    /**
     * 页数
     * @var int|string|mixed
     */
    public int|string $page = 1;

    /**
     * 每页条数
     * @var int|string|mixed
     */
    public int|string $pageSize = 35;

    /**
     * 版式  null：全部 1：横图；2：竖图；0：方图
     */
    public $ratio = 0;

    /**
     * 分类
     * @var int|string|mixed
     */
    public int|string $classId = 0;

    /**
     * true：强制回源
     * @var bool|mixed
     */
    public int $update = 0;

    /**
     * 【已废弃】
     * @var int|string|mixed
     */
    public int|string $size = 0;

    /**
     * 1：关键词（有交集）就会出现   但会降低性能  实测 3个词速度降低50%  8个词速度降低87.5%
     * @var int|string|mixed
     */
    public int|string $fuzzy = 0;

    /**
     * 模板类型 1.普通模板；2.GIF模板 3.ppt模板 4.视频模板 5.H5模板 & 长页H5
     * @var array|int[]|mixed
     */
    public array $templateTypes = [1, 2];

    /**
     * 颜色
     * @var array|mixed
     */
    public array $color = [];

    public $elasticsearchColor;

    /**
     * 【已废弃】
     * @var bool|mixed
     */
    public int $use = 0;

    /**
     * 宽度
     * @var int|string|mixed
     */
    public int|string $width = 0;

    /**
     * 高度
     * @var int|string|mixed
     */
    public int|string $height = 0;

    /**
     * 0：class查询规则（包含）才能出现  1：class查询规则（有交集）就会出现
     * @var int|string|mixed
     */
    public int|string $classIntersectionSearch = 0;

    public $sortClassId;

    public $sort;

    public $offset;

    protected $query = [];

    public function __construct($params)
    {
        $this->keyword = $params['keyword'] ?? null; // 防止不传值报错
        $this->kid1 = $params['kid1'] ?? 0;
        $this->kid2 = $params['kid2'] ?? 0;
        $this->sortType = $params['sort_type'] ?? 'default';
        $this->tagId = $params['tag_id'] ?? 0;
        $this->isZb = $params['is_zb'] ?? 1;
        $this->page = $params['page'] ?? 1;
        $this->pageSize = $params['page_size'] ?? 35;
        $this->ratio = $params['ratio'] ?? null;
        $this->classId = $params['class_id'] ?? 0;
        $this->update = $params['update'] ?? 0;
        $this->size = $params['size'] ?? 0;
        $this->fuzzy = $params['fuzzy'] ?? 0;
        $this->templateTypess = $params['template_type'] ?? [1, 2];
        $this->color = $params['color'] ?? [];
        $this->use = $params['user'] ?? 0;
        $this->width = $params['width'] ?? 0;
        $this->height = $params['height'] ?? 0;
        $this->classIntersectionSearch = $params['class_intersection_search'] ?? 0;
        $this->elasticsearchColor = $params['elasticsearch_color'] ?? '';
    }

    /**
     * 重新赋值
     */
    public function beforeAssignment()
    {
        $this->keyword = $this->keyword ?: null; // 防止为空
        $this->kid2 = $this->kid2 < 5000 ? $this->kid2 : 0;
        $this->classId = $this->classId ?: '0_0_0_0';
        $this->sortClassId = $this->classId;

        if ($this->keyword == null && !$this->tagId && $this->ratio <= 0 && ($this->classId == '0_0_0_0' || $this->classId == '0_0_0')) {
            //用于排序的class_id但不影响过滤项 影响全局排序的特殊class_id为-1
            $this->sortClassId = -1;
        }

        $this->classId = str_replace(
            ['10_133_0_', '132_133_0_', '10_550_27_'],
            ['31_23_0_', '31_23_0_', '32_27_326_'],
            $this->classId);
    }

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

    public function queryWidth()
    {
        if (!empty($this->width)) {
            $this->query['bool']['filter'][]['match']['width'] = $this->width;
        }
        return $this;
    }

    public function queryHeight()
    {
        if (!empty($this->width)) {
            $this->query['bool']['filter'][]['match']['width'] = $this->width;
        }
        return $this;
    }

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
            return $this->queryColor();
        }

        return $this->query;
    }
}
