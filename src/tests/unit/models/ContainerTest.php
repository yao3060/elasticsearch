<?php
namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Container;
use app\queries\ES\ContainerSearchQuery;
use yii\helpers\ArrayHelper;
class ContainerTest extends \Codeception\Test\Unit
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
    public function testSomeFeature()
    {
        $items = (new Container())
            ->search(new ContainerSearchQuery(
                keyword:0,
                page:1,
                pageSize:30,
                kid:0,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/apiv2/search-asset-container?word=&p=1&k2=0'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearch()
    {
        $items = (new Container())
            ->search(new ContainerSearchQuery(
                keyword:0,
                page:8,
                pageSize:30,
                kid:0,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/apiv2/search-asset-container?word=&p=8&k2=0'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
