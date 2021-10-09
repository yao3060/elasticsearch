<?php

use \Codeception\Util\HttpCode;

class SeoSearchWordAssetCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }
    /**
     * @param ApiTester $I
     * 有关键词测试
     */
    public function testGetSeoSearchWordAsset(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/seo/keyword-assets',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 30,
                'type' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_seo_search_word_asset_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'list' => [],
                    'total' => 'integer',
                ],
            ]
        );
    }
    /**
     * @param ApiTester $I
     * 无关键词测试
     */
    public function testGetSeoSearchWordAssetNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/seo/keyword-assets',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 30,
                'type' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_seo_search_word_asset_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'list' => [],
                    'total' => 'integer',
                ],
            ]
        );
    }
}
