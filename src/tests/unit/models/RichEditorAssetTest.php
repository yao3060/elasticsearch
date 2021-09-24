<?php
namespace models;

use app\components\IpsAuthority;
use app\models\ES\RichEditorAsset;
use app\queries\ES\RichEditorAssetSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class RichEditorAssetTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected static $urls = [
        'search' => '/rt-api/rt-asset-search',
        'search_carry_class_ids' => '/rt-api/rt-asset-search?class_ids=1,0&page=1&keyword=',
        'search_carry_class_ids_second' => '/rt-api/rt-asset-search?class_ids=2,55&page=1&keyword=',
        'search_carry_class_ids_page' => '/rt-api/rt-asset-search?class_ids=1,0&page=3&keyword=',
        'search_carry_keyword_class_ids' => '/rt-api/rt-asset-search?class_ids=5,58&page=1&keyword=%E6%A9%98%E8%89%B2'
    ];

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    public function prepareData(
        $keyword = 0,
        $classId = [],
        $page = 1,
        $pageSize = 40,
        $ratio = 0,
        $prodUrl = ''
    )
    {
        $search = (new RichEditorAsset)->search(new RichEditorAssetSearchQuery(
            keyword: $keyword,
            classId: $classId,
            page: $page,
            pageSize: $pageSize,
            ratio: $ratio
        ));

        $searchIds = $search['ids'] ?? [];

        if ($searchIds) {
            sort($searchIds);
        }

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = $responseJson['msg']['asset_list'] ?? [];

        if ($ids) {
            $ids = array_column($ids, 'id');
            sort($ids);
        }

        return [
            'dev' => $searchIds,
            'prod' => $ids
        ];
    }

    /**
     * @target: 默认，无搜索条件
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: [],
            page: "",
            ratio: "",
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target: 默认，无搜索条件
     * @classId: [1, 0]
     */
    public function testSearchClassIds()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: [1, 0],
            page: 1,
            ratio: "",
            pageSize: 40,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_class_ids']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target: 默认，无搜索条件
     * @classId: [2, 55]
     */
    public function testSearchClassIdsSecond()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: [2, 55],
            page: 1,
            ratio: "",
            pageSize: 40,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_class_ids_second']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target: 默认，无搜索条件
     * @classId: [2, 55]
     * @page: 2
     */
    public function testSearchClassIdsPage()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: [1, 0],
            page: 3,
            ratio: "",
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_class_ids_page']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 有搜索词：橘色
     * @classId: [5, 58]
     */
    public function testSearchCarryKeywordClassIds()
    {
        $compare = $this->prepareData(
            keyword: "橘色",
            classId: [5, 58],
            page: 1,
            ratio: "",
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_keyword_class_ids']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
