<?php

use Codeception\Util\HttpCode;

class SensitiveWordCest
{
    public function _before(ApiTester $I)
    {
        $I->haveHttpHeader('host', 'es-api-staging.818ps.com');
        $I->haveHttpHeader('accept', 'application/json');
        $I->haveHttpHeader('content-type', 'application/json');
    }

    /**
     * @target 违规搜索词：党政
     */
    public function testValidateSensitiveWordPartyPolicy(ApiTester $I)
    {
        $I->sendPost(API_TESTING_BASE_URL . 'v1/sensitive-words/validate', [
            'keyword' => '党政'
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'sensitive_word_validate',
            'message' => 'Sensitive Word Validate'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'flag' => 'boolean'
           ]
       ]);
    }

    /**
     * @target 违规搜索词：毛泽东
     */
    public function testValidateSensitiveWordMaoZeDong(ApiTester $I)
    {
        $I->sendPost(API_TESTING_BASE_URL . 'v1/sensitive-words/validate', [
            'keyword' => '毛泽东'
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 'sensitive_word_validate',
            'message' => 'Sensitive Word Validate'
        ]);
        $I->seeResponseMatchesJsonType([
           'code' => 'string',
           'message' => 'string',
           'data' => [
               'flag' => 'boolean'
           ]
       ]);
    }
}
