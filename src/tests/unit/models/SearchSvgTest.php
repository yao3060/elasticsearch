<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Svg;
use app\queries\ES\SvgSearchQuery;
use yii\helpers\ArrayHelper;

class SearchSvgTest extends \Codeception\Test\Unit
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
        IpsAuthority::definedAuth(); // 初始化权限变量
        $this->http = new \GuzzleHttp\Client();
    }

    protected function _after()
    {
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
        sort($ids);

        // remove empty elements
        $ids = array_filter($ids, fn ($id) => !is_null($id) && $id !== '');

        $myIds = $items['ids'];
        sort($myIds);

        return [
            'dev' => join(',', $myIds),
            'prod' => join(',', $ids)
        ];
    }

    // tests
    public function testSearchSVG()
    {
        $data = $this->prepareData(
            0,
            1,
            [],
            50,
            'http://818ps.com/apiv2/search-asset-svg?p=1&k2=0&word=&pageSize=50'
        );

        $this->assertEquals($data['dev'], $data['prod']);
    }

    public function testSearchSVGHeart()
    {
        $data = $this->prepareData(
            '心',
            1,
            [],
            50,
            'http://818ps.com/apiv2/search-asset-svg?p=1&k2=0&word=%E5%BF%83&pageSize=50'
        );
        $this->assertEquals($data['dev'], $data['prod']);
    }
}
