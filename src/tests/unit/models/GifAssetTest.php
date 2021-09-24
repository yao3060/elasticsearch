<?php
namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\GifAsset;
use app\queries\ES\GifAssetSearchQuery;
use yii\helpers\ArrayHelper;

class GifAssetTest extends \Codeception\Test\Unit
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
        $items = (new GifAsset())
            ->search(new GifAssetSearchQuery(
                keyword: '你好',
                page: 1,
                pageSize: 40,
                classId: 10,
                isZb: 1,
                prep: 1,
                limitSize: 0
            ));
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-gif-asset-list?keyword=你好&p=1&class_id=10'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchTwo()
    {
        $items = (new GifAsset())
            ->search(new GifAssetSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 40,
                classId: 10,
                isZb: 1,
                prep: 1,
                limitSize: 0
            ));
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-gif-asset-list?keyword=&p=1&class_id=10'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchThree()
    {
        $items = (new GifAsset())
            ->search(new GifAssetSearchQuery(
                keyword: '寒露',
                page: 1,
                pageSize: 40,
                classId: 10,
                isZb: 1,
                prep: 1,
                limitSize: 0
            ));
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-gif-asset-list?keyword=寒露&p=1&class_id=10'
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
        $items = (new GifAsset())
            ->search(new GifAssetSearchQuery(
                keyword: 0,
                page: 3,
                pageSize: 40,
                classId: 10,
                isZb: 1,
                prep: 1,
                limitSize: 0
            ));
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-gif-asset-list?keyword=&p=3&class_id=10'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchFive()
    {
        $items = (new GifAsset())
            ->search(new GifAssetSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 40,
                classId: 0,
                isZb: 0,
                prep: 1,
                limitSize: 0
            ));
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-gif-asset-list?is_h5=1&keyword=&p=1&class_id=0'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
   /* public function testSearchSix()
    {
        $items = (new GifAsset())
            ->search(new GifAssetSearchQuery(
                keyword: 0,
                page: 1,
                pageSize: 40,
                classId: 19,
                isZb: 0,
                prep: 1,
                limitSize: 0
            ));
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/api/get-gif-asset-list?keyword=&p=1&class_id=19'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }*/
}
