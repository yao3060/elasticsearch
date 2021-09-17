<?php
namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\SeoSearchWordAsset;
use app\queries\ES\SeoSearchWordAssetQuery;
use yii\helpers\ArrayHelper;
class SeoSearchWordAssetTest extends \Codeception\Test\Unit
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
        $items = (new SeoSearchWordAsset())
            ->seoSearch(new SeoSearchWordAssetQuery(
                keyword: '早安',
                page: 1,
                pageSize: 10,
                type: 2,
            ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/beijing/zaoan.html?route_id=16317766697739&route=16&after_route='
            //https://818ps.com/png/nihao.html
        );

       /* $content = json_decode($response->getBody()->getContents());
        var_dump($content);exit();
        $ids = ArrayHelper::getColumn($content, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));*/
    }
}
