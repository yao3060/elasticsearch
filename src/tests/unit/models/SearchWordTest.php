<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\SearchWord;
use app\queries\ES\SearchWordSearchQuery;
use yii\helpers\ArrayHelper;

class SearchWordTest extends \Codeception\Test\Unit
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
        $items = (new SearchWord())
            ->search(new SearchWordSearchQuery(
                keyword: '你好',
                pageSize: 20,
                type: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/site/sphinx?1=1&keyword=%E4%BD%A0%E5%A5%BD&type=1&max=6'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
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
        $items = (new SearchWord())
            ->search(new SearchWordSearchQuery(
                keyword: '再见',
                pageSize: 20,
                type: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/site/sphinx?1=1&keyword=%E5%86%8D%E8%A7%81&type=1&max=6'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
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
        $items = (new SearchWord())
            ->search(new SearchWordSearchQuery(
                keyword: '中秋',
                pageSize: 20,
                type: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/site/sphinx?1=1&keyword=中秋&type=1&max=6'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
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
        $items = (new SearchWord())
            ->search(new SearchWordSearchQuery(
                keyword: '我们',
                pageSize: 20,
                type: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/site/sphinx?1=1&keyword=我们&type=1&max=6'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content, 'id');
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
