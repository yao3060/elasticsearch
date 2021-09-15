<?php
namespace tests\unit\models;

use app\components\IpsAuthority;
use yii\helpers\ArrayHelper;
class VideoETest extends \Codeception\Test\Unit
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

    public function testSomeFeature()
    {
        $items = (new SearchWord())
            ->search(new SearchWordSearchQuery(
                keyword:'你好',
                pageSize:20,
                type:1,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            'https://818ps.com/site/sphinx?1=1&keyword=%E4%BD%A0%E5%A5%BD&type=1&max=6'
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
            }else {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals($flag,1);
    }
}
