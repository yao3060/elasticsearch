<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Template;
use app\queries\ES\TemplateRecommendSearchQuery;
use app\queries\ES\TemplateSearchQuery;
use GuzzleHttp\Client;

class TemplateTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var client
     */
    protected $client;

    protected function _before()
    {
        IpsAuthority::definedAuth(); // 初始化权限变量

        $this->client = new Client();
    }

    protected function _after()
    {
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
        $elasticsearchColor = '',
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
            classIntersectionSearch: $classIntersectionSearch,
            elasticsearchColor: $elasticsearchColor
        ));

        sort($search['ids']);

        $response = $this->client->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        if (isset($responseJson['data']['templInfo']) && $responseJson['data']['templInfo']) {
            $ids = array_column($responseJson['data']['templInfo'], 'id');
        }

        if (isset($responseJson['msg']) && $responseJson['msg']) {
            $ids = array_column($responseJson['msg'], 'id');
        }

        sort($ids);

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];
    }

    /**
     * 推荐搜索data
     * @param int $keyword
     * @param int $page
     * @param int $pageSize
     * @param null $templateType
     * @param null $ratio
     * @param string $prodUrl
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function prepareRecommendSearchData(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $templateType = null,
        $ratio = null,
        $prodUrl = ''
    )
    {
        $recommendSearch = (new Template())->recommendSearch(new TemplateRecommendSearchQuery(
            keyword: $keyword,
            page: $page,
            pageSize: $pageSize,
            templateType: $templateType,
            ratio: $ratio
        ));

        sort($recommendSearch['ids']);

        $responose = $this->client->get($prodUrl);

        $responseJson = json_decode($responose->getBody()->getContents(), true);

    }

    /**
     * 测试搜索模板
     * @throws \GuzzleHttp\Exception\GuzzleException
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
            prodUrl: getenv('UNIT_BASE_URL') . '/apiv2/get-ppt-template-list?sort_type=bytime'
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * 测试带有搜索词的模板
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
            ratio: 0,
            classId: "10_30_0",
            templateTypes: 1,
            fuzzy: 1,
            size: 0,
            update: 0,
            width: 1242,
            height: 2208,
            classIntersectionSearch: 1,
            prodUrl: getenv('UNIT_BASE_URL') . '/api/get-template-list?w=%E4%BD%A0%E5%A5%BD&p=1&kid_1=0&kid_2=0&ratioId=0&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=10_30_0&width=1242&height=2208',
        );

        $this->assertEqualsCanonicalizing($compareKeywordResult['dev'], $compareKeywordResult['prod']);
    }

    /**
     * 测试搜索推荐服务
     */
//    public function testRecommendSearch()
//    {
//
//        $compareRecommend = $this->prepareRecommendSearchData(
//            prodUrl: getenv('UNIT_BASE_URL') . '/api/get-template-list-v2'
//        );
//
//        $this->assertEquals();
//    }
}
