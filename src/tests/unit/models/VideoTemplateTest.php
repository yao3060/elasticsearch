<?php

namespace models;

use app\components\IpsAuthority;
use app\models\ES\VideoTemplate;
use app\queries\ES\VideoTemplateSearchQuery;
use Codeception\Test\Unit;
use GuzzleHttp\Client;
use tests\unit\models\BaseTest;

class VideoTemplateTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    public function prepareData(
        $keyword = "",
        $classId = [],
        $page = 1,
        $pageSize = 40,
        $ratio = null,
        $prep = 0,
        $prodUrl = ''
    )
    {
        $search = (new VideoTemplate())->search(new VideoTemplateSearchQuery(
            keyword: $keyword,
            classId: $classId,
            page: $page,
            pageSize: $pageSize,
            ratio: $ratio,
            prep: $prep
        ));

        $devIds = [];
        if (isset($search['ids']) && $search['ids']) {
            $devIds = $search['ids'];
            sort($devIds);
        }

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = [];

        if (isset($responseJson['msg']) && $responseJson['msg']) {
            $ids = array_column($responseJson['msg'], 'id');

            sort($ids);
        }

        return [
            'dev' => $devIds,
            'prod' => $ids
        ];
    }

    /**
     * @target 默认，无搜索词搜索
     */
    public function testSearch()
    {
        $compare = $this->prepareData(
            classId: [],
            pageSize: 32,
            ratio: '',
            prodUrl: getenv('UNIT_BASE_URL') . '/api-video/get-excerpt-list'
        );

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
