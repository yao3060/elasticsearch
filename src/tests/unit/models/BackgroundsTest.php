<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Background;
use app\queries\ES\BackGroundSearchQuery;
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
    }
    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {
        $items = (new Background())
            ->search(new BackGroundSearchQuery(
                keyword:'你好',
                page:1,
                pageSize:30,
                sceneId:0,
                isZb:0,
                sort:0,
                useCount: 0,
                kid: 0,
                ratioId: 0,
                isBg: 1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/api/get-asset-list?w=%E4%BD%A0%E5%A5%BD&p=1&type=background&k1=0&k2=0&k3=0&tagId=undefined&sceneId=0&styleId=0&ratioId=-1'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
