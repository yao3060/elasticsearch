<?php

use \Codeception\Util\HttpCode;

class H5SensitiveWordCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }
    /**
     * @param ApiTester $I
     * 无敏感词测试
     */
    public function testGetH5SensitiveWord(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendPost(
            API_TESTING_BASE_URL . 'v1/h5-sensitive-words/validate',
            [
                'keyword' => "你好",
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_h5_ban_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'flag' => 'boolean',
                    'word' => 'string',
                ],
            ]
        );
    }
    /**
     * @param ApiTester $I
     * 有敏感词测试
     */
    public function testGetH5SensitiveWordNull(ApiTester $I)
    {
        // pass in query params in second argument
        $I->sendPost(
            API_TESTING_BASE_URL . 'v1/h5-sensitive-words/validate',
            [
                'keyword' => 'fuck',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['code' => 'get_h5_ban_list']);
        $I->seeResponseMatchesJsonType(
            [
                'code' => 'string',
                'message' => 'string',
                'data' => [
                    'flag' => 'boolean',
                    'word' => 'string',
                ],
            ]
        );
    }
}
