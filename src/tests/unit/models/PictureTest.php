<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Background;
use app\models\ES\Picture;
use app\queries\ES\BackGroundSearchQuery;
use app\queries\ES\PictureSearchQuery;
use yii\helpers\ArrayHelper;

class PictureTest extends \Codeception\Test\Unit
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
        $items = (new Picture())
            ->search(new PictureSearchQuery(
                keyword:'早安',
                page:1,
                pageSize:30,
                sceneId:0,
                isZb:1,
                kid:0,
                vipPic: 0,
                ratioId:-1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/api/get-asset-list?w=%E6%97%A9%E5%AE%89&p=1&type=pic&k1=0&k2=0&k3=0&tagId=undefined&sceneId=undefined&styleId=0&ratioId=undefined&isPic=true&picId=4689234'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearch()
    {
        $items = (new Picture())
            ->search(new PictureSearchQuery(
                keyword:0,
                page:1,
                pageSize:30,
                sceneId:0,
                isZb:1,
                kid:0,
                vipPic: 0,
                ratioId:-1
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/api/get-asset-list?w=&p=1&type=pic&k1=0&k2=0&k3=0&tagId=undefined&sceneId=undefined&styleId=0&ratioId=undefined&isPic=true&picId=4689234'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
