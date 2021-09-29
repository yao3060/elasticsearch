<?php
namespace models;

use app\components\IpsAuthority;
use app\models\ES\LottieVideo;
use app\queries\ES\LottieVideoSearchQuery;
use GuzzleHttp\Client;

class LottieVideoTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected static $urls = [
        'search' => '/video/lottie-search',
        'search_class_id_1_page_1' => '/video/lottie-search?keyword=&class_id=1&page=1',
        'search_keyword_class_id_1_page_1' => '/video/lottie-search?keyword=%E5%8F%AF%E7%88%B1&class_id=1&page=1',
        'search_class_id_3_page_1' => '/video/lottie-search?keyword=&class_id=3&page=1'
    ];

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    protected function _after()
    {
    }

    public function prepareData(
        $keyword = 0,
        $classId = [],
        $page = 1,
        $pageSize = 60,
        $prep = 0,
        $produrl = ''
    )
    {
        $search = (new LottieVideo())->search(new LottieVideoSearchQuery(
            keyword: $keyword,
            classId: $classId,
            page: $page,
            pageSize: $pageSize,
            prep: $prep
        ));

        $searchIds = $search['ids'] ?? [];

        if ($searchIds) sort($searchIds);

        $response = (new Client())->get($produrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = $responseJson['msg'] ?? [];

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
     * @target 默认搜索
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: "",
            produrl: getenv('UNIT_BASE_URL') . self::$urls['search']
        );

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 无搜索词搜索
     * @classId: 1
     * @page: 1
     */
    public function testSearchClassIdOfOnePageOfOne()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: 1,
            page: 1,
            produrl: getenv('UNIT_BASE_URL') . self::$urls['search_class_id_1_page_1']
        );

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 有搜索词：可爱
     * @classId: 1
     * @page: 1
     */
    public function testSearchCarryKeywordClassIdOfOnePageOfOne()
    {
        $compare = $this->prepareData(
            keyword: "可爱",
            classId: [],
            page: 1,
            produrl: getenv('UNIT_BASE_URL') . self::$urls['search_keyword_class_id_1_page_1']
        );

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @taget 无搜索关键词
     * @classId: 3
     * @page: 1
     */
    public function testSearchClassIdOfThreePageOfOne()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: [3],
            page: 1,
            produrl: getenv('UNIT_BASE_URL') . self::$urls['search_class_id_3_page_1']
        );

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
