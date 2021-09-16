<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Svg;
use app\queries\ES\SvgSearchQuery;
use Codeception\Test\Unit;
use yii\helpers\ArrayHelper;

class SearchSvgTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \GuzzleHttp\Client
     */
    private $http;

    protected function _before()
    {
        IpsAuthority::definedAuth();
        $this->http = new \GuzzleHttp\Client();
    }

    protected function prepareData(
        $keyword = 0,
        $page = 1,
        $kid2 = [],
        $pageSize = 50,
        $prod_api_uri = ''
    ) {
        $items = (new Svg)->search(new SvgSearchQuery(
            keyword: $keyword,
            page: $page,
            kid2: $kid2,
            pageSize: $pageSize
        ));

        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request('GET', $prod_api_uri);

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');

        // remove empty elements
        $ids = array_filter($ids, fn ($id) => !is_null($id) && $id !== '');

        return [
            'dev' => $items['ids'],
            'prod' => $ids
        ];
    }

    /**
     * @target 默认，无搜索条件
     */
    public function testSearchSVG()
    {
        $data = $this->prepareData(
            0,
            1,
            [],
            50,
            getenv('UNIT_BASE_URL') . '/apiv2/search-asset-svg?p=1&k2=0&word=&pageSize=50'
        );

        $this->assertEqualsCanonicalizing($data['prod'], $data['dev'],);
    }

    /**
     * @target 搜索关键词：心
     * @pageSize 50
     */
    public function testSearchSVGHeart()
    {
        $data = $this->prepareData(
            '心',
            1,
            [],
            50,
            getenv('UNIT_BASE_URL') . '/apiv2/search-asset-svg?p=1&k2=0&word=%E5%BF%83&pageSize=50'
        );
        $this->assertEqualsCanonicalizing($data['prod'], $data['dev']);
    }
}
