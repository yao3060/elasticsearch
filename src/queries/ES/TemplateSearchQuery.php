<?php

namespace app\queries\ES;

use app\components\IpsAuthority;
use app\components\Tools;
use app\interfaces\ES\QueryBuilderInterface;
use app\models\ES\Template;

class TemplateSearchQuery implements QueryBuilderInterface
{
    /**
     * 搜索关键词
     * @var string|mixed
     */
    public $keywords;

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
    public array $templateType = [1, 2];

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

    private $query;


    public function __construct($params)
    {
        $this->keywords = $params['keyword'] ?? null;
        $this->kid1 = $params['kid1'] ?? 0;
        $this->kid2 = $params['kid2'] ?? 0;
        $this->sortType = $params['sortType'] ?? 'default';
        $this->tagId = $params['tagId'] ?? 0;
        $this->isZb = $params['isZb'] ?? 1;
        $this->page = $params['page'] ?? 1;
        $this->pageSize = $params['pageSize'] ?? 35;
        $this->ratio = $params['ratio'] ?? 0;
        $this->classId = $params['classId'] ?? 0;
        $this->update = $params['update'] ?? 0;
        $this->size = $params['size'] ?? 0;
        $this->fuzzy = $params['fuzzy'] ?? 0;
        $this->templateType = $params['templateType'] ?? [1, 2];
        $this->color = $params['color'] ?? [];
        $this->use = $params['user'] ?? 0;
        $this->width = $params['width'] ?? 0;
        $this->height = $params['height'] ?? 0;
        $this->classIntersectionSearch = $params['classIntersectionSearch'] ?? 0;
        $this->elasticsearchColor = $params['elasticsearchColor'] ?? '';
    }

    public function getRedisKey()
    {
        $redisKey = "ES_template12-23:";
        if ($this->fuzzy == 1) {
            $redisKey .= ":fuzzy";
        }

        $implodeKeyArr = [
            $this->keywords, $this->page, $this->kid1, $this->kid2, $this->sortType,
            $this->tagId, $this->isZb, $this->pageSize,
            $this->ratio, $this->classId, $this->size, $this->use
        ];

        $redisKey .= ":" . implode('_', $implodeKeyArr);

        if (!empty($this->templateType) && is_array($this->templateType)) {
            $redisKey .= '_' . implode('|', $this->templateType);
        } else if ($this->templateType > 0) {
            $redisKey .= "_" . $this->templateType;
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

    public function makeOffset()
    {
        if ($this->page * $this->pageSize > 10000) {
            $this->pageSize = $this->pageSize - ($this->page * $this->pageSize - 10000) % $this->pageSize;
            $offset = 10000 - $this->pageSize;
        } else {
            $offset = ($this->page - 1) * $this->pageSize;
        }
        return $offset;
    }

    public function makeKid1()
    {
        if ($this->kid1) {
            if ($this->kid1 != 1) {
                $this->query['bool']['must'][]['match']['kid_1'] = $this->kid1;
            }
        }
    }

    public function makeKid2()
    {
        if ($this->kid2) {
            if ($this->kid2 == 21) {
                //公众号配图特殊化处理
                $this->kid2 = [20, 21, 22];
                $this->query['bool']['must'][]['terms']['kid_2'] = $this->kid2;
            } else {
                $this->query['bool']['must'][]['match']['kid_2'] = $this->kid2;
            }
        }
    }

    public function makeRatio()
    {
        if ($this->ratio != null) {
            $this->query['bool']['must'][]['match']['ratio'] = $this->ratio;
        }
    }

    public function query(): array
    {
        $this->kid2 = $this->kid2 < 5000 ? $this->kid2 : 0;
        $this->ratio = ($this->ratio != null && is_numeric($this->ratio)) ? $this->ratio : null;
        $classId = $this->classId ?? '0_0_0_0';
        $classId = $classId != 'undefined' ? $classId : '0_0_0_0';
        $sortClassId = $classId;

        if ($this->keywords == null && !$this->tagId && $this->ratio <= 0
            && ($classId == '0_0_0_0' || $classId == '0_0_0')) $sortClassId = -1;

        $classId = str_replace(
            ['10_133_0_', '132_133_0_', '10_550_27_'],
            ['31_23_0_', '31_23_0_', '32_27_326_'],
            $classId);

        $this->classId = $classId;

        $redisKey = $this->getRedisKey();

        $offset = $this->makeOffset();

        $reStartTime = microtime(true);

        if (!IpsAuthority::check(IOS_ALBUM_USER)) {
            $return = Tools::getRedis(Template::$redis_db, $redisKey);
        }

        $redis_stat['st'] = (int)((microtime(true) - $reStartTime) * 1000);
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
        $redis_stat['key'] = 'ES_template12-23:' . $caller . ($this->fuzzy == 1 ? 1 : null);
        $redis_stat['hit'] = 1;

//        if (!$return || Tools::isReturnSource() || $this->update == 1) {
        if (!$return || $this->update == 1) {
            // 把回源从不命中中去除
            if (!$return || !$return['total']) {
                $redis_stat['hit'] = 0;
            }
            $reStartTime = microtime(true);
            if ($this->keywords != null) {
                $this->query = Template::queryKeyword($this->keywords, $this->fuzzy);
            }
            unset($return);

            $this->makeKid1();

            $this->makeKid2();

            $this->makeRatio();

            if ($classId) {
                $classId = explode('_', $classId);
                //class_id 过滤 为空 或者 不是数字 或者 小于等于0 都从分类arr中剔除
                foreach ($classId as $key => $item) {
                    if (empty($item) || !is_numeric($item) || $item <= 0) {
                        unset($classId[$key]);
                    }
                }
                $classId = array_values($classId);

                if (in_array(760, $classId)) {
                    //视频模板要特殊处理
                    $this->templateType = 4;
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

            if (count($this->templateType) > 1 && in_array(5, $this->templateType)) {
                //有H5就添加长页H5(不改变key,除了单独搜索H5类型)
                $this->templateType[] = 7;
            }

            if (!empty($this->templateType) && is_array($this->templateType)) {
                // 如果搜索全部类型模板则去掉该条件
                if (count($this->templateType) < 7) {
                    $this->query['bool']['must'][]['terms']['template_type'] = $this->templateType;
                }
            } else if ($this->templateType > 0) {
                $this->query['bool']['must'][]['match']['template_type'] = $this->templateType;
            }

            if ($this->tagId) {
                $this->tagId = explode('_', $this->tagId);
                foreach ($this->tagId as &$item) {
                    $item = (int)$item;
                }
                $this->query['bool']['filter'][]['terms']['tag_id'] = $this->tagId;
            }

            if ($this->isZb >= 1) {
                //可商用
                $this->query['bool']['filter'][]['range']['is_zb']['gte'] = 1;
            } elseif ($this->isZb === '_1') {
                //不可商用
                $this->query['bool']['filter'][]['match']['is_zb'] = 0;
            }

            if (IpsAuthority::check(IOS_ALBUM_USER)) {
                $this->query['bool']['must_not'][]['match']['hide_in_ios'] = 1;
            }

            if (!empty($width)) {
                $this->query['bool']['filter'][]['match']['width'] = $width;
            }
            if (!empty($height)) {
                $this->query['bool']['filter'][]['match']['height'] = $height;
            }

            switch ($this->sortType) {
                case 'bytime':
                    $sort = Template::sortByTime();
                    break;
                case 'byyesday':
                    $sort = Template::sortByYesday();
                    $this->query['bool']['filter'][]['range']['web_dl']['gt'] = 0; //获取昨日有下载量的模板
                    break;
                case 'byweekday':
                    $sort = Template::sortByWeekday();
                    $this->query['bool']['filter'][]['range']['week_web_dl']['gt'] = 0;
                    break;
                case 'bymonth':
                    $sort = Template::sortByMonth();
                    $this->query['bool']['filter'][]['range']['month_web_dl']['gt'] = 0;
                    break;
                case 'byhot':
                    $sort = Template::sortByHot();
                    break;
                default:
                    $sort = Template::sortDefault($this->keywords, $sortClassId);
                    break;
            }

            //颜色搜索
            if ($this->color) {
                list($colorRange, $colorParams) = Template::formatColor(array_column($this->color, 'color'), array_column($this->color, 'weight'));
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

                $this->query = [
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

        }

        return [
            'query' => $this->query ?? [],
            'offset' => $offset,
            'sort' => $sort ?? [],
            'redisKey' => $redisKey,
            'reStartTime' => $reStartTime,
            'return' => $return ?? []
        ];
    }
}
