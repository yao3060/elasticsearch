<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\GroupWords;
use app\queries\ES\GroupWordsSearchQuery;
use yii\helpers\ArrayHelper;

class GroupWordsTest extends \Codeception\Test\Unit
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
        $items = (new GroupWords())
            ->search(new GroupWordsSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 30,
                search: '你好',
                searchAll: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/search-groupword-list?w=&p=1&search=%E4%BD%A0%E5%A5%BD&all=1'
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
        //search和keyword为0时，searchAll要等1
        $items = (new GroupWords())
            ->search(new GroupWordsSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 30,
                search: 0,
                searchAll: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/search-groupword-list?w=&p=1&search=&all=1'
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
        $items = (new GroupWords())
            ->search(new GroupWordsSearchQuery(
                keyword: 0,
                page: 2,
                pageSize: 30,
                search: '网址',
                searchAll: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/search-groupword-list?w=&p=2&search=%E7%BD%91%E5%9D%80&all=1'
        );

        $content = json_decode($response->getBody()->getContents());
        if (isset($items['ids']) && $content->msg != 'noting') {
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
        } else {
            $this->assertEquals(1, 1);
        }

    }
    public function testSearchFour()
    {
        $items = (new GroupWords())
            ->search(new GroupWordsSearchQuery(
                keyword: 0,
                page: 2,
                pageSize: 30,
                search: '中秋',
                searchAll: 1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/search-groupword-list?w=&p=2&search=中秋&all=1'
        );

        $content = json_decode($response->getBody()->getContents());
        if (isset($items['ids']) && $content->msg != 'noting') {
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
        } else {
            $this->assertEquals(1, 1);
        }

    }
}
