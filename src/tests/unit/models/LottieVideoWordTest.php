<?php
namespace models;

use app\components\IpsAuthority;
use app\models\ES\LottieVideoWord;
use app\queries\ES\LottieVideoWordSearchQuery;
use GuzzleHttp\Client;

class LottieVideoWordTest extends \Codeception\Test\Unit
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

        if (!empty($responseJson['msg'])) $ids = array_column($responseJson['msg'], 'id');

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];

    }

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
