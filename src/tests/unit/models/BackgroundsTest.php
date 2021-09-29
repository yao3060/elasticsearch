<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Background;
use app\queries\ES\BackgroundSearchQuery;
use yii\helpers\ArrayHelper;

class BackgroundsTest extends \Codeception\Test\Unit
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
        //$this->secondSomeFeature();
    }

    protected function _after()
    {
    }

    // tests
    public function testSearchOne()
    {
        $items = (new Background())
            ->search(new BackgroundSearchQuery(
                keyword: '国庆',
                page: 3,
                pageSize: 30,
                sceneId: 0,
                isZb: 1,
                sort: 0,
                useCount: 0,
                kid: 0,
                ratioId: -1,
                isBg: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') . '/api/get-asset-list?w=国庆&p=3&type=background&k1=0&k2=0&k3=0&tagId=undefined&sceneId=0&styleId=0&ratioId=-1'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }

    public function testSearchTwo()
    {
        $items = (new Background())
            ->search(new BackgroundSearchQuery(
                keyword: '再见',
                page: 1,
                pageSize: 30,
                sceneId: 0,
                isZb: 1,
                sort: 0,
                useCount: 0,
                kid: 0,
                ratioId: -1,
                isBg: 0
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') . '/api/get-asset-list?w=再见&p=1&type=background&k1=0&k2=0&k3=0&tagId=undefined&sceneId=0&styleId=0&ratioId=-1'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchThree()
    {
        $items = (new Background())
            ->search(new BackgroundSearchQuery(
                keyword: '我们',
                page: 1,
                pageSize: 30,
                sceneId: 0,
                isZb: 1,
                sort: 0,
                useCount: 0,
                kid: 0,
                ratioId: -1,
                isBg: 0
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') . '/api/get-asset-list?w=我们&p=1&type=background&k1=0&k2=0&k3=0&tagId=undefined&sceneId=0&styleId=0&ratioId=-1'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchFour()
    {
        $items = (new Background())
            ->search(new BackgroundSearchQuery(
                keyword: '中秋',
                page: 1,
                pageSize: 30,
                sceneId: 0,
                isZb: 1,
                sort: 0,
                useCount: 0,
                kid: 0,
                ratioId: -1,
                isBg: 0
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/api/get-asset-list?w=中秋&p=1&type=background&k1=0&k2=0&k3=0&tagId=undefined&sceneId=0&styleId=0&ratioId=-1'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
