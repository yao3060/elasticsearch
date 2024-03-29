<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Asset;
use app\queries\ES\AssetSearchQuery;
use yii\helpers\ArrayHelper;

class AssetTest extends \Codeception\Test\Unit
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
    public function testSearchOne()
    {
        $items = (new Asset())
            ->search(new AssetSearchQuery(
                keyword: '你好',
                page: 1,
                pageSize: 30,
                sceneId: 0,
                isZb: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-asset-list?w=%E4%BD%A0%E5%A5%BD&p=1&type=image&k1=0&k2=0&k3=0&tagId=0&sceneId=undefined&styleId=undefined&ratioId=undefined'
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
        $items = (new Asset())
            ->search(new AssetSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 30,
                sceneId: 0,
                isZb: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-asset-list?w=&p=1&type=image&k1=0&k2=0&k3=0&tagId=0&sceneId=undefined&styleId=undefined&ratioId=undefined'
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
        $items = (new Asset())
            ->search(new AssetSearchQuery(
                keyword: '你好',
                page: 3,
                pageSize: 30,
                sceneId: 0,
                isZb: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-asset-list?w=%E4%BD%A0%E5%A5%BD&p=3&type=image&k1=0&k2=0&k3=0&tagId=0&sceneId=undefined&styleId=undefined&ratioId=undefined'
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
        $items = (new Asset())
            ->search(new AssetSearchQuery(
                keyword: '我们',
                page: 1,
                pageSize: 30,
                sceneId: 0,
                isZb: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-asset-list?w=我们&p=1&type=image&k1=0&k2=0&k3=0&tagId=0&sceneId=undefined&styleId=undefined&ratioId=undefined'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchFive()
    {
        $items = (new Asset())
            ->search(new AssetSearchQuery(
                keyword: '国庆',
                page: 2,
                pageSize: 30,
                sceneId: 0,
                isZb: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-asset-list?w=国庆&p=2&type=image&k1=0&k2=0&k3=0&tagId=0&sceneId=undefined&styleId=undefined&ratioId=undefined'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
