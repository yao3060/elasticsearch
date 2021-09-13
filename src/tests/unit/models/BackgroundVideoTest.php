<?php

namespace app\tests\unit\models;

use app\components\IpsAuthority;
use app\models\ES\BackgroundVideo;
use app\queries\ES\BackgroundVideoQuery;
use GuzzleHttp\Client;

class BackgroundVideoTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth(); // 初始化权限变量
    }

    protected function _after()
    {
    }

    protected function prepareData(
        $keyword = 0,
        $classId = [],
        $page = 1,
        $pageSize = 1,
        $ratio = 0,
        $prodUrl = ''
    )
    {
        $search = (new BackgroundVideo())->search(new BackgroundVideoQuery(
            keyword: $keyword,
            page: $page,
            ratio: $ratio,
            classId: $classId,
            pageSize: $pageSize,
        ));

        sort($search['ids']);

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = array_column($responseJson['msg'], 'id');

        sort($ids);

        return [
            'dev' => $ids,
            'prod' => $search['ids']
        ];
    }

    /**
     * h5-api 无关键词搜索
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: '',
            page: '',
            ratio: '',
            classId: [4],
            pageSize: 40,
            prodUrl: getenv('UNIT_BASE_URL') . '/h5-api/bg-video-search'
        );

        $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }

    /**
     * video 无关键词搜索
     */
    public function testVideoSearch()
    {
        $compareVideo = $this->prepareData(
            keyword: '',
            page: '',
            ratio: '',
            classId: "",
            pageSize: 40,
            prodUrl: getenv('UNIT_BASE_URL') . '/video/bg-video-search'
        );

        $this->assertEqualsCanonicalizing($compareVideo['dev'], $compareVideo['prod']);
    }
}
