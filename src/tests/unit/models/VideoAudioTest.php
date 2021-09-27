<?php

namespace tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\VideoAudio;
use app\queries\ES\VideoAudioSearchQuery;
use yii\helpers\ArrayHelper;

class VideoAudioTest extends \Codeception\Test\Unit
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
        $items = (new VideoAudio())
            ->search(new VideoAudioSearchQuery(
                         keyword: 0,
                         page: 1,
                         pageSize: 40,
                         parentsId: 0,
                         classId: 6,
                         prep: 0,
                         isDesigner: 1,
                         isVip: 0,
                     ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/video/audio-search?keyword=&class_id=6&page=1&is_vip=&isDesigner=1'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg->list, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
    public function testSearchTwo()
    {
        $items = (new VideoAudio())
            ->search(new VideoAudioSearchQuery(
                         keyword: '轻音乐',
                         page: 1,
                         pageSize: 40,
                         parentsId: 0,
                         classId: 0,
                         prep: 0,
                         isDesigner: 0,
                         isVip: 0,
                     ));
        /**@var \GuzzleHttp\Psr7\Response $response */
        $response = $this->http->request(
            'GET',
            getenv('UNIT_BASE_URL') .'/video/audio-search?keyword=%E8%BD%BB%E9%9F%B3%E4%B9%90&class_id=&page=1'
        );
        $content = json_decode($response->getBody()->getContents());
        $ids = ArrayHelper::getColumn($content->msg->list, 'id');
        sort($ids);
        $myIds = $items['ids'];
        sort($myIds);
        $this->assertEquals(join(',', $ids), join(',', $myIds));
    }
}
