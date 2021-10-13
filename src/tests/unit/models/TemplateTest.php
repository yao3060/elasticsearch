<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Template;
use app\queries\ES\TemplateSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class TemplateTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected static $urls = [
        'search' => '/apiv2/get-ppt-template-list?sort_type=bytime',
        'search_page_two' => '/apiv2/get-ppt-template-list?keyword=&p=2&class_id=290_0_0&sort_type=&tag_id=0',
        'search_carry_class_ids_tag_id' => '/apiv2/get-ppt-template-list?keyword=&p=1&class_id=290_334_0_0&sort_type=&tag_id=46',
        'search_carry_class_ids_tag_id_sort_type' => '/apiv2/get-ppt-template-list?keyword=&p=1&class_id=290_334_0_0&sort_type=bytime&tag_id=46',
        'search_carry_keyword' => '/api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208',
        'search_carry_keyword_class_ids' => '/apiv2/get-ppt-template-list?keyword=%E7%8E%AF%E4%BF%9D&p=1&class_id=290_0_0_0&sort_type=&tag_id=0',
        'search_carry_keyword_class_ids_tag_id' => '/apiv2/get-ppt-template-list?keyword=%E7%8E%AF%E4%BF%9D&p=1&class_id=290_0_0_710&sort_type=&tag_id=49',
        'search_carry_keyword_class_ids_tag_id_second' => '/apiv2/get-ppt-template-list?keyword=%E7%8E%AF%E4%BF%9D&p=1&class_id=290_0_0_710&sort_type=&tag_id=104',
    ];

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    /**
     * 构建ppt模板列表搜索测试参数
     */
    public function prepareData(
        $keyword = null,
        $page = 1,
        $kid1 = 0,
        $kid2 = 0,
        $sortType = 'default',
        $tagId = 0,
        $isZb = 1,
        $pageSize = 35,
        $ratio = null,
        $classId = 0,
        $update = 0,
        $size = 0,
        $fuzzy = 0,
        $templateTypes = [1, 2],
        $use = 0,
        $color = [],
        $width = 0,
        $height = 0,
        $classIntersectionSearch = 0,
        $prodUrl = ''
    )
    {
        $search = (new Template())->search(new TemplateSearchQuery(
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
            use: $use,
            color: $color,
            width: $width,
            height: $height,
            classIntersectionSearch: $classIntersectionSearch
        ));

        $searchIds = $search['ids'] ?? [];

        if ($searchIds) {
            sort($searchIds);
        }

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = $responseJson['data']['templInfo'] ?? [];

        if ($ids && $responseJson['stat'] != -1) {
            $ids = array_column($ids, 'id');
            sort($ids);
        }

        return [
            'dev' => $searchIds,
            'prod' => $ids
        ];
    }

    /**
     * @target 默认，无搜索关键词
     * @templateTypes 3
     * @pageSize  35
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: "",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: 'bytime',
            tagId: "",
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 测试无搜索词有分类分页
     * @classId: 290_0_0
     * @page: 2
     */
    public function testSearchPageOfTwo()
    {
        $compare = $this->prepareData(
            keyword: "",
            page: 2,
            kid1: 0,
            kid2: 0,
            sortType: '',
            tagId: 0,
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "290_0_0",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_page_two']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 测试无搜索词
     * @classId: 290_334_0_0
     * @tagId: 46
     */
    public function testSearchCarryClassIdsTagId()
    {
        $compare = $this->prepareData(
            keyword: "",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: '',
            tagId: 46,
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "290_334_0_0",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_class_ids_tag_id']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 测试无搜索词
     * @classId: 290_334_0_0
     * @sortType: 'bytime'
     * @tagId: 46
     */
    public function testSearchCarryClassIdsSortTypeTagId()
    {
        $compare = $this->prepareData(
            keyword: "",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: 'bytime',
            tagId: 46,
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "290_334_0_0",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_class_ids_tag_id_sort_type']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 搜索词：你好
     * @classId 10_30_0
     * @width 1242
     * @height 2208
     */
    public function testSearchCarryKeyword()
    {
//        $compareKeywordResult = $this->prepareData(
//            keyword: "你好",
//            page: 1,
//            kid1: 0,
//            kid2: 0,
//            sortType: "",
//            tagId: "",
//            isZb: 1,
//            pageSize: 32,
//            ratio: 0,
//            classId: "10_30_0",
//            templateTypes: 1,
//            fuzzy: 0,
//            size: 0,
//            update: 0,
//            prodUrl: getenv('UNIT_BASE_URL') . '/api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208',
//        );

        $compareKeywordResult = $this->prepareData(
            keyword: "你好",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: "",
            tagId: "",
            isZb: 0,
            pageSize: 32,
            ratio: "",
            classId: "10_30_0",
            templateTypes: 1,
            size: 0,
            update: 0,
            width: 1242,
            height: 2208,
            classIntersectionSearch: 1,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_keyword'],
        );

        $this->assertEqualsCanonicalizing($compareKeywordResult['dev'], $compareKeywordResult['prod']);
    }

    /**
     * @target 关键词搜索：环保
     * @classId: 290_0_0_0
     * @tagId: 0
     */
    public function testSearchCarryKeywordClassIdsSortTypeTagId()
    {
        $compare = $this->prepareData(
            keyword: "环保",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: '',
            tagId: 0,
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "290_0_0_0",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_keyword_class_ids']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 关键词搜索：环保
     * @classId: 290_0_0_710
     * @tagId: 49
     */
    public function testSearchCarryKeywordClassIdsTagId()
    {
        $compare = $this->prepareData(
            keyword: "环保",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: '',
            tagId: 49,
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "290_0_0_710",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_keyword_class_ids_tag_id']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 关键词搜索：环保
     * @classId: 290_0_0_710
     * @tagId: 49
     */
    public function testSearchCarryKeywordClassIdsTagIdSecond()
    {
        $compare = $this->prepareData(
            keyword: "环保",
            page: 1,
            kid1: 0,
            kid2: 0,
            sortType: '',
            tagId: 104,
            isZb: 1,
            pageSize: 35,
            ratio: "",
            classId: "290_0_0_710",
            update: 0,
            size: 0,
            templateTypes: 3,
            fuzzy: 0,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_keyword_class_ids_tag_id_second']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
