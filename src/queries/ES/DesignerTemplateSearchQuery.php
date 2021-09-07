<?php

namespace app\queries\ES;

use Yii;

class DesignerTemplateSearchQuery extends BaseTemplateSearchQuery
{

    protected $sortClassId;
    protected $templateAttr;
    protected $settlementLevel;
    protected $query = [];

    const REDIS_DB = "_search";

    //每个搜索结果存1万条 1天过期
    const REDIS_KEY = "ES:template:second:designer:";
    // 页数，未使用
    const HASH_KEY_SECOND_PAGE = "is:second:hash:template:second:page";

    public static $esKey = null;
    public $sort;
    public int $offset;

    public function __construct(
        public string $keyword = '0',
        public int $page = 1,
        public int $kid1 = 0,
        public int $kid2 = 0,
        public string $sortType = 'default',
        public string $tagId = '',
        public int $isZb = 1,
        public int $pageSize = 100,
        public ?string $ratio = null,
        public string $classId = '',
        public int $update = 0,
        public int $size = 0,
        public int $fuzzy = 0,
        public array $templateTypes = [1, 2],
        public array $templateInfo = [],
        public array $color = [],
        public int $use = 0
    ) {
        $this->sortClassId = $classId;
    }

    public static function index()
    {
        return 'second_design';
    }

    public function query(): array
    {
        if (
            !$this->keyword &&
            !$this->tagId &&
            $this->ratio <= 0 &&
            in_array($this->classId, ['0_0_0_0', '0_0_0'])
        ) {
            //用于排序的class_id但不影响过滤项 影响全局排序的特殊class_id为-1
            $this->sortClassId = -1;
        }

        //ips_template_info => templ_attr 模板属性 1普通模板  2精品模板  3GIF模板  4套图模板
        $this->templateAttr = $this->templateInfo['templ_attr'] ?? 0;

        //ips_template_info => 结算等级 1=>A级    2=>S级'
        $this->settlementLevel = $this->templateInfo['settlement_level'] ?? 0;

        $this->offset = ($this->page - 1) * $this->pageSize;

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
        $classId = str_replace(
            ['10_133_0_', '132_133_0_', '10_550_27_'],
            ['31_23_0_', '31_23_0_', '32_27_326_'],
            $this->classId
        );
        $redisKey = self::REDIS_KEY . date('Y-m-d');
        if ($this->fuzzy == 1) {
            $redisKey .= ":fuzzy";
        }

        // $redisKey:主图厨房用具_156_301_default_0_1_10000__0_0_0_0_0_0_0_0_4_1
        $redisKey = sprintf(
            $redisKey . ':%s_%d_%d_%s_%s_%d_%d_%s_%s_%d_%d_%d',
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
            $this->templateAttr,
            $this->settlementLevel
        );
        //templateTypes = [1,2]
        if (!empty($this->templateTypes)) {
            $redisKey .= '_' . implode('|', $this->templateTypes);
        }

        if ($this->color) {
            $redisKey .= '_' . implode(',', array_column($this->color, 'color')) .
                '_' . implode(',', array_column($this->color, 'weight'));
        }

        //获取页数 占用逻辑
        switch ($this->templateInfo['type']) {
            case 'second':
                self::$esKey = $redisKey;
                $page = Yii::$app->redis8->hget(self::HASH_KEY_SECOND_PAGE, self::REDIS_KEY);
                return $page ?: 1;
            default:
                $redisKey .= "_" . $this->page;
                self::$esKey = $redisKey;
                return $redisKey;
        }
    }


    /**
     * query function
     *
     * @param string $keyword
     * @param int $isOr
     * @return $this
     */
    protected function queryKeyword()
    {
        $operator = $this->fuzzy > 0 ? 'or' : 'and';
        switch ($operator):
            case 'and':
                $keyword = $this->keyword;
                $fields = ["title^16", "description^2", "hide_description^2", "brief^2", "info^1"];
                break;
            case 'or':
                $keyword = str_replace(['图片'], '', $this->keyword);
                $fields = ["title^16", "description^2", "hide_description^2", "info^1"];
                break;
        endswitch;

        if (in_array($keyword, ['LOGO', 'logo'])) {
            $fields = ["title^16", "description^2", "hide_description^2", "info^1"];
        }
        $this->query['bool']['must'][]['multi_match'] = [
            'query' => $keyword,
            'fields' => $fields,
            'type' => 'most_fields',
            "operator" => $operator
        ];
        return $this;
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

            default: // @todo
                $this->sort = $this->sortDefault(
                    $this->keyword,
                    $this->sortClassId,
                    static::index()
                );
        }
    }
}
