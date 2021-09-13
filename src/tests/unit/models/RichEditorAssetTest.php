<?php
namespace models;

use app\components\IpsAuthority;
use app\models\ES\RichEditorAsset;
use app\queries\ES\RtAssetSearchQuery;
use GuzzleHttp\Client;

class RichEditorAssetTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        IpsAuthority::definedAuth();
    }

    protected function _after()
    {
    }

    public function prepareData(
        $keyword = 0,
        $classId = [],
        $page = 1,
        $pageSize = 40,
        $ratio = 0,
        $prodUrl = ''
    )
    {
        $search = (new RichEditorAsset)->search(new RtAssetSearchQuery(
            keyword: $keyword,
            classId: $classId,
            page: $page,
            pageSize: $pageSize,
            ratio: $ratio
        ));

        sort($search['ids']);

        $response = (new Client())->get($prodUrl);

        $responseJson = json_decode($response->getBody()->getContents(), true);

        $ids = array_column($responseJson['msg']['asset_list'], 'id');

        return [
            'dev' => $search['ids'],
            'prod' => $ids
        ];
    }

    public function testSearch()
    {
        $compare = $this->prepareData(
            keyword: "",
            classId: [],
            page: "",
            ratio: "",
            prodUrl: getenv('UNIT_BASE_URL') . '/rt-api/rt-asset-search'
        );

        return $this->assertEqualsCanonicalizing($compare['dev'], $compare['prod']);
    }
}
