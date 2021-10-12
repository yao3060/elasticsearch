<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\Asset;
use app\models\ES\Svg;
use app\queries\ES\AssetSearchQuery;
use app\queries\ES\SvgSearchQuery;
use yii\helpers\ArrayHelper;

class SvgTest extends \Codeception\Test\Unit
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
        $items = (new Svg())
            ->search(new SvgSearchQuery(
                         keyword: 0,
                         page: 1,
                         pageSize: 30,
                         kid2: 0,
                     ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/apiv2/search-asset-svg?p=1&k2=0&word='
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
