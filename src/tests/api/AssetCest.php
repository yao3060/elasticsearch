<?php

use \Codeception\Util\HttpCode;

class AssetCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    // tests
    public function testGetAssets(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(API_TESTING_BASE_URL . 'v1/assets', [
            "keyword" => "你好",
            "page" => 3, "page_size" => 30, "scene_id" => 0, "is_zb" => 1
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_asset_list']);
        $I->seeResponseMatchesJsonType([
            'code' => 'string',
            'message' => 'string',
            'data' => [
                'hit' => 'integer',
                'ids' => 'array',
                'score' => 'array',
            ],
        ]);
    }
}
