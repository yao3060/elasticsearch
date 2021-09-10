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
    public function testSomeFeature()
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
            'https://818ps.com/api/search-groupword-list?w=&p=1&search=%E4%BD%A0%E5%A5%BD&all=1'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
