<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\DesignerTemplate;
use app\queries\ES\DesignerTemplateSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class DesignerTemplateTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    protected static $urls = [
        'search' => '/api/get-template-list?w=&p=1&kid_1=1&kid_2=19&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=&width=200&height=200&es_type=1',
        'search_carry_keyword' => '/api/get-template-list?w=%E4%B8%BB%E5%9B%BE&p=1&kid_1=156&kid_2=301&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=0&es_type=3',
        'search_normal_es_type_of_three' => '/api/get-template-list?w=&p=1&kid_1=156&kid_2=157&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=0&es_type=3'
    ];

    protected function prepareData(
        $keyword = 0,
        $page = 1,
        $kid1 = 0,
        $kid2 = 0,
        $sortType = 'default',
        $tagId = 0,
        $isZb = 1,
        $pageSize = 100,
        $ratio = null,
        $classId = 0,
        $update = 0,
        $size = 0,
        $fuzzy = 0,
        $templateTypes = [1, 2],
        $templInfo = [],
        $color = [],
        $use = 0,
        $produrl = ''
    )
    {
        $search = (new DesignerTemplate())->search(new DesignerTemplateSearchQuery(
            keyword: $keyword,
            page: $page,
            kid1: $kid1,
            kid2: $kid2,
            sortType: $sortType,
            tagId: $tagId,
            isZb: $isZb,
            pageSize: $pageSize,
            ratio: $ratio,
            classId: $classId,
            update: $update,
            size: $size,
            fuzzy: $fuzzy,
            templateTypes: $templateTypes,
            templateInfo: $templInfo,
            color: $color,
            use: $use,
        ));

        if (sizeof($search['ids'])) sort($search['ids']);

        $response = (new Client())->get($produrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = [];

        if (!empty($responseJson['msg'])) {
            $ids = array_column($responseJson['msg'], 'id');
        }

        $intersect = array_intersect($search['ids'], $ids);

        return count($intersect) === count($ids);
    }

    protected function queryTemplateIds(
        $keyword = 0,
        $page = 1,
        $kid1 = 0,
        $kid2 = 0,
        $sortType = 'default',
        $tagId = 0,
        $isZb = 1,
        $pageSize = 100,
        $ratio = null,
        $classId = 0,
        $update = 0,
        $size = 0,
        $fuzzy = 0,
        $templateTypes = [1, 2],
        $templInfo = [],
        $color = [],
        $use = 0,
        $produrl = ''
    )
    {
        $search = (new DesignerTemplate())->getTemplateIds(
            keyword: $keyword,
            page: $page,
            kid1: $kid1,
            kid2: $kid2,
            sortType: $sortType,
            tagId: $tagId,
            isZb: $isZb,
            pageSize: $pageSize,
            ratio: $ratio,
            classId: $classId,
            update: $update,
            size: $size,
            fuzzy: $fuzzy,
            templateTypes: $templateTypes,
            templInfo: $templInfo,
            color: $color,
            use: $use
        );

        if (isset($search['ids']) && $search['ids']) sort($search['ids']);

        $response = (new Client())->get($produrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = [];

        if (isset($responseJson['msg']) && $responseJson['msg']) {
            $ids = array_column($responseJson['msg'], 'id');
            sort($ids);
        }

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];
    }

    /*
     * @target 测试 DesignerTemplate@getTemplateIds
     * @notice 只适用于搜索结果数量小于 32 条【不改变测试代码的情况下】
     * @reason 搜索条件一致，搜索结果顺序不一致
     */
    public function testSearch()
    {
        $compare = $this->queryTemplateIds(
            keyword: "",
            kid1: 1,
            kid2: 19,
//            sortType: "",
//            tagId: "",
            isZb: 0,
            pageSize: 10000,
//            ratio: "",
//            classId: "",
            templateTypes: 1,
            templInfo: [
            "picId" => "",
            "templ_attr" => 2,
            "type" => "second"
        ],
            color: [],
            produrl: getenv("UNIT_BASE_URL") . self::$urls['search']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 测试有搜索词
     * @kid1 156
     * @kid2 301
     * @es_type 3
     * @template_type 4
     */
    public function testSearchCarryKeyword()
    {
        $compareKeyword = $this->prepareData(
            keyword: "主图",
            kid1: 156,
            kid2: 301,
            sortType: "default",
            tagId: 0,
            isZb: 1,
            page: 1,
            pageSize: 10000,
            ratio: "",
            classId: "0_0_0_0",
            templateTypes: 4,
            templInfo: [
                "picId" => ""
            ],
            color: [],
            produrl: getenv("UNIT_BASE_URL") . self::$urls['search_carry_keyword']
        );

        $this->assertTrue($compareKeyword);
    }

    /**
     * @target 测试无搜索词
     * @kid1 156
     * @kid2 157
     * @es_type 3
     */
    public function testSearchNormalEsTypeOfThree()
    {
        $compareNormal = $this->prepareData(
            kid1: 156,
            kid2: 157,
            sortType: "default",
            tagId: 0,
            isZb: 1,
            pageSize: 10000,
            ratio: "",
            classId: "0_0_0_0",
            templateTypes: 4,
            templInfo: [
                "picId" => ""
            ],
            color: [],
            produrl: getenv("UNIT_BASE_URL") . self::$urls['search_normal_es_type_of_three']
        );

        $this->assertTrue($compareNormal);
    }
}
