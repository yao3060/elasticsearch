<?php

use \Codeception\Util\HttpCode;

class SeoSearchWordCest
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
    public function testGetSeoSearchWord(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/seo/keywords',
            [
                'keyword' => "你好",
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_seo_search_word_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'is_seo_search_keyword' => 'boolean',
                    'id' => "string",
                    'keyword' => 'string',
                ],
            ]
        );
    }
    /**
     * @param ApiTester $I
     * 关键词测试
     */
    public function testGetSeoSearchWordNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/seo/keywords',
            [
                'keyword' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_seo_search_word_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'is_seo_search_keyword' => 'boolean',
                    'id' => "string",
                    'keyword' => 'string',
                ],
            ]
        );
    }
}
