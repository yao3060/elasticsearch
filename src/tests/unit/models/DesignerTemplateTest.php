<?php

namespace models;

use app\components\IpsAuthority;
use app\models\ES\DesignerTemplate;
use app\queries\ES\DesignerTemplateSearchQuery;
use GuzzleHttp\Client;

class DesignerTemplateTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    protected function _after()
    {

    }

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
        $templateType = [1, 2],
        $templInfo = [],
        $color = [],
        $use = 0,
        $prodUrl = ''
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
            templateTypes: $templateType,
            templInfo: $templInfo,
            color: $color,
            use: $use,
        );

        if (sizeof($search['ids'])) sort($search['ids']);

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = [];

        if (!empty($responseJson['msg'])) {
            $ids = array_column($responseJson['msg'], 'id');
            sort($ids);
        }

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];

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
        }

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];
    }

    /*
     * search [normal]
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: "",
            kid1: 1,
            kid2: 19,
            sortType: "",
            tagId: "",
            isZb: 0,
            pageSize: 10000,
            ratio: -1,
            classId: "",
            templateType: 1,
            templInfo: [
                "picId" => "",
                "templ_attr" => 2,
                "type" => "second"
            ],
            color: [],
            prodUrl: getenv("UNIT_BASE_URL") . "/api/get-template-list?w=&p=1&kid_1=1&kid_2=19&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=&width=200&height=200&es_type=1"
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * search 携带 keyword 参数
     */
    public function testSearchCarryKeyword()
    {
        $compareKeyword = $this->queryTemplateIds(
            keyword: "主图",
            kid1: 156,
            kid2: 301,
            sortType: "",
            tagId: "",
            isZb: 0,
            pageSize: 10000,
            ratio: -1,
            classId: 0,
            templateTypes: 4,
            templInfo: [
                "picId" => "",
                "type" => "second"
            ],
            color: [],
            produrl: getenv("UNIT_BASE_URL") . "/api/get-template-list?w=%E4%B8%BB%E5%9B%BE&p=1&kid_1=156&kid_2=301&ratioId=-1&tag1=0&tag2=0&tag3=0&sort_type=&is_zb=0&class_id=0&es_type=3"
        );

        $this->assertEqualsCanonicalizing($compareKeyword['dev'], $compareKeyword['prod']);
    }
}
