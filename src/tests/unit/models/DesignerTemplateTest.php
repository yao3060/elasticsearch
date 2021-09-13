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

        sort($search['ids']);

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = array_column($responseJson['msg'], 'id');

        sort($ids);

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];

    }

    // tests
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
            ratio: "",
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

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
