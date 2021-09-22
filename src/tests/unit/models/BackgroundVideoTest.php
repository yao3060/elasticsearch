<?php

namespace app\tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\BackgroundVideo;
use app\queries\ES\BackgroundVideoQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class BackgroundVideoTest extends Unit
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
        'h5_search' => '/h5-api/bg-video-search',
        'video_search' => '/video/bg-video-search',
        'video_search_carry_keyword' => '/video/bg-video-search?keyword=%E6%8F%92%E7%94%BB&class_id=0&page=1&ratio=1&pageSize=30',
        'video_search_carry_keyword_business' => '/video/bg-video-search?keyword=%E5%95%86%E5%8A%A1&class_id=0&page=1&ratio=2&pageSize=30',
        'video_search_carry_keyword_invitation' => '/video/bg-video-search?keyword=%E9%82%80%E8%AF%B7%E5%87%BD&class_id=0&page=3&ratio=2&pageSize=30',
        'video_search_page_of_nine' => '/video/bg-video-search?keyword=&class_id=&page=1&ratio=2&pageSize=9'
    ];

    protected function prepareData(
        $keyword = 0,
        $classId = [],
        $page = 1,
        $pageSize = 1,
        $ratio = 0,
        $prodUrl = ''
    )
    {
        $search = (new BackgroundVideo())->search(new BackgroundVideoQuery(
            keyword: $keyword,
            page: $page,
            ratio: $ratio,
            classId: $classId,
            pageSize: $pageSize,
        ));

        $searchIds = $search['ids'] ?? [];

        if ($searchIds) sort($searchIds);

        $response = (new Client())->get($prodUrl);

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
     * @target 无搜索词，h5-api搜索
     * @h5-api h5 api controller 无关键词搜索
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: '',
            page: '',
            ratio: '',
            classId: [4],
            pageSize: 40,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['h5_search']
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 无搜索词，默认搜索条件
     * @video video controller 无关键词搜索
     */
    public function testVideoSearch()
    {
        $compareVideo = $this->prepareData(
            keyword: '',
            page: '',
            ratio: '',
            classId: "",
            pageSize: 40,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['video_search']
        );

        $this->assertEqualsCanonicalizing($compareVideo['dev'], $compareVideo['prod']);
    }

    /**
     * @target 有搜索词，比例为 1
     * @video 关键词搜索：插画
     * @ratio 1 比例
     */
    public function testVideoSearchCarryKeyword()
    {
        $compareVideoKeyword = $this->prepareData(
            keyword: '插画',
            page: 1,
            ratio: 1,
            classId: 0,
            pageSize: 30,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['video_search_carry_keyword']
        );

        $this->assertEqualsCanonicalizing($compareVideoKeyword['dev'], $compareVideoKeyword['prod']);
    }

    /**
     * @target 有搜索词，比例为 2
     * @video video controller 关键词搜索：商务
     * @ratio 2 比例
     */
    public function testVideoSearchCarryKeywordBusiness()
    {
        $compareVideoKeyword = $this->prepareData(
            keyword: '商务',
            page: 1,
            ratio: 2,
            classId: 0,
            pageSize: 30,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['video_search_carry_keyword_business']
        );

        $this->assertEqualsCanonicalizing($compareVideoKeyword['dev'], $compareVideoKeyword['prod']);
    }

    /**
     * @target 有搜索词，页码自定义
     * @video video controller 关键词搜索：邀请函
     * @ratio 2 比例
     * @page 3 页码
     */
    public function testVideoSearchCarryKeywordInvitation()
    {
        $compareVideoKeyword = $this->prepareData(
            keyword: '邀请函',
            page: 3,
            ratio: 2,
            classId: 0,
            pageSize: 30,
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['video_search_carry_keyword_invitation']
        );

        $this->assertEqualsCanonicalizing($compareVideoKeyword['dev'], $compareVideoKeyword['prod']);
    }

    /**
     * @target 测试无搜索词分页自定义
     * @video video controller
     * @ratio 2 比例
     * @page 1 页码
     * @pageSize 9 每页展示数量
     */
    public function testVideoSearchPageOfNine()
    {
        $compareVideoKeyword = $this->prepareData(
            keyword: "",
            page: 1,
            pageSize: 9,
            ratio: 2,
            classId: "",
            prodUrl: getenv('UNIT_BASE_URL') . self::$urls['video_search_page_of_nine']
        );

        $this->assertEqualsCanonicalizing($compareVideoKeyword['dev'], $compareVideoKeyword['prod']);
    }
}
