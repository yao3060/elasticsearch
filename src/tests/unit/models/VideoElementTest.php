<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\VideoElement;
use app\queries\ES\VideoElementSearchQuery;
use yii\helpers\ArrayHelper;

class VideoElementTest extends \Codeception\Test\Unit
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
        $items = (new VideoElement())
            ->search(new VideoElementSearchQuery(
                         keyword: '可爱',
                         page: 1,
                         pageSize: 40,
                         classId: '188',
                         ratio: 0,
                         scopeType: 'lottie',
                         owner:0
                     ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/video/video-e-search?keyword=可爱&class_id=188&page=1&scope_type=lottie'
        );

        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);

        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
