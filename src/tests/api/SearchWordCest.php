<?php

use \Codeception\Util\HttpCode;

class SearchWordCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    // tests
    public function testGetSearchWord(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/keywords',
            [
                'keyword' => "你好",
                'page' => 1,
                'page_size' => 30,
                'type' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_search_word_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }

    public function testGetSearchWordNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendGet(
            API_TESTING_BASE_URL . 'v1/keywords',
            [
                'keyword' => '',
                'page' => 1,
                'page_size' => 30,
                'type' => 1,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_search_word_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'hit' => 'integer',
                    'ids' => 'array',
                    'score' => 'array',
                ],
            ]
        );
    }
}
