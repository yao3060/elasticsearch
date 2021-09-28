<?php

namespace app\queries\ES;

use app\models\ES\Template;
use Yii;

class DesignerTemplateSearchQuery extends BaseTemplateSearchQuery
{

    protected $sortClassId;
    protected $templateAttr;
    protected $settlementLevel;

    //每个搜索结果存1万条 1天过期
    const REDIS_KEY = "ES:template:second:designer:";
    // 页数，未使用
    const HASH_KEY_SECOND_PAGE = "is:second:hash:template:second:page";

    public static $esKey = null;
    public $sort;
    public int $offset;

    public function __construct(
        public $keyword = 0,
        public $page = 1,
        public $kid1 = 0,
        public $kid2 = 0,
        public $sortType = 'default',
        public $tagId = 0,
        public $isZb = 1,
        public $pageSize = 100,
        public $ratio = null,
        public $classId = 0,
        public $update = 0,
        public $size = 0,
        public $fuzzy = 0,
        public $templateTypes = [1, 2],
        public $templateInfo = [],
        public $color = [],
        public $use = 0
    )
    {
        $this->sortClassId = $classId;
    }

    public static function index()
    {
        return 'second_design';
    }

    public function beforeAssignment()
    {
        $this->sortType = $this->sortType ?: 'default';
        $this->keyword = $this->keyword ?: 0;
        $this->kid1 = $this->kid1 ? $this->kid1 : 0;
        $this->kid2 = $this->kid2 ? $this->kid2 : 0;
        $this->tagId = $this->tagId ? $this->tagId : 0;
        $this->isZb = $this->isZb ? $this->isZb : 1;
        $this->ratio = $this->ratio != null ? $this->ratio : null;
        $this->classId = $this->classId ? $this->classId : '0_0_0_0';
        $this->sortClassId = $this->classId;
        $this->size = $this->size ? $this->size : 0;
        $this->use = $this->use ? $this->use : 0;
        $this->templateAttr = isset($this->templateInfo['templ_attr']) ? $this->templateInfo['templ_attr'] : 0; //ips_template_info => templ_attr 模板属性 1普通模板  2精品模板  3GIF模板  4套图模板
        $this->settlementLevel = isset($this->templateInfo['settlement_level']) && $this->templateInfo['settlement_level'] ? $this->templateInfo['settlement_level'] : 0; //ips_template_info => 结算等级 1=>A级    2=>S级'
        if (!$this->keyword && !$this->tagId && $this->ratio <= 0 && in_array($this->classId, ['0_0_0_0', '0_0_0'])) {
            //用于排序的class_id但不影响过滤项 影响全局排序的特殊class_id为-1
            $this->sortClassId = -1;
        }

        $this->offset = ($this->page - 1) * $this->pageSize;
    }

    public function query(): array
    {
        $this->queryKeyword()
            ->queryTemplateAttr()
            ->querySettlementLevel()
            ->queryKid1()
            ->queryKid2()
            ->queryRatio()
            ->queryClassIds()
            ->queryTemplateTypes()
            ->queryTagIds()
            ->queryIsZb()
            ->queryIosAlbumUser();

        $this->setSort();

        //颜色搜索
        if ($this->color) {
            return $this->queryColor();
        }

        return $this->query;
    }

    //获取搜索redis key
    public function getRedisKey()
    {
        $this->beforeAssignment();

        $classId = str_replace(
            ['10_133_0_', '132_133_0_', '10_550_27_'],
            ['31_23_0_', '31_23_0_', '32_27_326_'],
            $this->classId
        );

        $redisKey = self::REDIS_KEY . date('Y-m-d');
        if ($this->fuzzy == 1) {
            $redisKey .= ":fuzzy";
        }

        $implodeKeys = [
            $this->keyword,
            $this->kid1,
            $this->kid2,
            $this->sortType,
            $this->tagId,
            $this->isZb,
            $this->pageSize,
            $this->ratio,
            $classId,
            $this->size,
            $this->use,
            $this->templateAttr,
            $this->settlementLevel
        ];

        $redisKey .= ':' . implode('_', $implodeKeys);

        //templateTypes = [1,2]
        if (!empty($this->templateTypes) && is_array($this->templateTypes)) {
            $redisKey .= '_' . implode('|', $this->templateTypes);
        } else if ($this->templateTypes > 0) {
            $redisKey .= "_" . $this->templateTypes;
        }

        if (!empty($this->color) && $this->color) {
            $redisKey .= '_' . implode(',', array_column($this->color, 'color')) .
                '_' . implode(',', array_column($this->color, 'weight'));
        }

        //获取页数 占用逻辑
        if (isset($this->templateInfo['type']) && $this->templateInfo['type'] == 'second') {
            self::$esKey = $redisKey;
            $page = Yii::$app->redis8->hget(self::HASH_KEY_SECOND_PAGE, self::REDIS_KEY);
            return $page ?: 1;
        }

        $redisKey .= "_" . $this->page;
        self::$esKey = $redisKey;
        return $redisKey;
    }

    protected function queryColor()
    {
        if ($this->color) {
            list($colorRange, $colorParams) = $this->formatColor(
                array_column($this->color, 'color'),
                array_column($this->color, 'weight')
            );
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
    }

    protected function setSort()
    {
        switch ($this->sortType) {
            case 'bytime':
                $this->sort = $this->sortByTime();
                break;

            case 'byhot':
                $this->sort = $this->sortByHot();
                break;

            default:
                $this->sort = $this->sortDefault(
                    $this->keyword,
                    $this->sortClassId,
                    static::index()
                );
        }
    }
}
