<?php
namespace models;

use app\components\IpsAuthority;
use app\models\ES\LottieVideoWord;
use app\queries\ES\LottieVideoWordSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;

class LottieVideoWordTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    public function prepareData(
        $keyword = 0,
        $page = 1,
        $pageSize = 40,
        $prep = 0,
        $prodUrl = ''
    )
    {
        $search = (new LottieVideoWord())->search(new LottieVideoWordSearchQuery(
            keyword: $keyword, page: $page, pageSize: $pageSize, prep: $prep
        ));

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

    /**
     * @target 默认，无搜索条件
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: "",
            page: 1,
            pageSize: 40,
            prep: 0,
            prodUrl: getenv('UNIT_BASE_URL') . '/video/lottie-word-search'
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * @target 搜索词：风景
     */
    public function testSearchCarryKeyword()
    {
        $compare = $this->prepareData(
            keyword: "风景",
            page: 1,
            pageSize: 40,
            prep: 0,
            prodUrl: getenv('UNIT_BASE_URL') . '/video/lottie-word-search?keyword=' . urlencode('风景')
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
