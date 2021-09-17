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

    public function testSearchOne()
    {
        $items = (new Container())
            ->search(new ContainerSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 30,
                kid: 0,
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
        $flag = 1;
        foreach ($ids as $va) {
            if (in_array($va, $myIds)) {
                continue;
            } else {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals($flag, 1);
    }

    public function testSearchTwo()
    {
        $items = (new Container())
            ->search(new ContainerSearchQuery(
                keyword: 0,
                page: 8,
                pageSize: 30,
                kid: 0,
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
        $flag = 1;
        foreach ($ids as $va) {
            if (in_array($va, $myIds)) {
                continue;
            } else {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals($flag, 1);
    }
    public function testSearchThree()
    {
        $items = (new Container())
            ->search(new ContainerSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 30,
                kid: 0,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/apiv2/search-asset-container?p=1&k2=0&word= '
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $flag = 1;
        foreach ($ids as $va) {
            if (in_array($va, $myIds)) {
                continue;
            } else {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals($flag, 1);
    }
    public function testSearchFour()
    {
        $items = (new Container())
            ->search(new ContainerSearchQuery(
                keyword: 0,
                page: 4,
                pageSize: 30,
                kid: 0,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/apiv2/search-asset-container?p=4&k2=0&word= '
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $flag = 1;
        foreach ($ids as $va) {
            if (in_array($va, $myIds)) {
                continue;
            } else {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals($flag, 1);
    }
}
