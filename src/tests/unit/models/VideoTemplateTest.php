<?php

namespace models;

use app\components\IpsAuthority;
use app\models\ES\VideoTemplate;
use app\queries\ES\VideoTemplateSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class VideoTemplateTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected static $urls = [
        'search' => '/api-video/get-excerpt-list',
        'search_carry_keyword' => '/api-video/get-excerpt-list?w=%E6%95%99%E5%B8%88%E8%8A%82&p=1&class_id=&ratio=2',
        'search_carry_class_ids_page_two' => '/api-video/get-excerpt-list?w=&p=2&class_id=1579-1580&ratio=1',
        'search_carrd_keyword_class_ids_none' => '/api-video/get-excerpt-list?w=%E4%B8%A2%E5%A4%B1&p=1&class_id=&ratio=1'
    ];

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    public function prepareData(
        $keyword = "",
        $classId = [],
        $page = 1,
        $pageSize = 40,
        $ratio = null,
        $prep = 0,
        $prodUrl = ''
    )
    {
        $search = (new VideoTemplate())->search(new VideoTemplateSearchQuery(
            keyword: $keyword,
            classId: $classId,
            page: $page,
            pageSize: $pageSize,
            ratio: $ratio,
            prep: $prep
        ));

        $devIds = $search['ids'] ?? [];
        if ($devIds) {
            sort($devIds);
        }

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = $responseJson['msg'] ?? [];

        if ($ids && $responseJson['stat'] != -1) {
            $ids = array_column($ids, 'id');
            sort($ids);
        } else {
            $ids = [];
        }

        return [
            'dev' => $devIds,
            'prod' => $ids
        ];
    }

    /**
     * @target 默认，无搜索词搜索
     * @tips 其余参数默认
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            classId: [],
            pageSize: 32,
            ratio: '',
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 有搜索词：教师节
     * @ratio: 2
     */
    public function testSearchCarryKeyword()
    {
        $compare = $this->prepareData(
            keyword: '教师节',
            classId: [],
            pageSize: 32,
            ratio: 2,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_keyword']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 无搜索词
     * @classId: [1579, 1580]
     * @page: 2
     */
    public function testSearchCarryClassIdsPageOfTwo()
    {
        $compare = $this->prepareData(
            classId: [1579, 1580],
            page:2,
            pageSize: 32,
            ratio: 1,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carry_class_ids_page_two']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 有搜索词：丢失
     * @classId: []
     */
    public function testSearchCarryKeywordClassIdsOfNone()
    {
        $compare = $this->prepareData(
            keyword: '丢失',
            page:1,
            ratio: 1,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['search_carrd_keyword_class_ids_none']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
