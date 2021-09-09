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

    // tests
    public function testSearchSVG()
    {
        $items = (new Svg)->search(new SvgSearchQuery(
            keyword: 0,
            page: 1,
            kid2: [],
            pageSize: 50
        ));

        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/apiv2/search-asset-svg?p=1&k2=0&word=&pageSize=50'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);

        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }

    public function testSearchSVGHeart()
    {
        $items = (new Svg)->search(new SvgSearchQuery(
            keyword: '心',
            page: 1,
            kid2: [],
            pageSize: 50
        ));

        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/apiv2/search-asset-svg?p=1&k2=0&word=%E5%BF%83&pageSize=50'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);

        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
